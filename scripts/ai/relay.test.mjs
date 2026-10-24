import test from 'node:test';
import assert from 'node:assert/strict';
import {mkdtemp,mkdir,writeFile,readFile,rm,realpath,symlink} from 'node:fs/promises';
import os from 'node:os';import path from 'node:path';
import {main,runOnce,initializeAndRun,RelayConflict,projectDirectory,httpTransport,buildPrompt} from './relay.mjs';
async function fixture(t) {const dir=await realpath(await mkdtemp(path.join(os.tmpdir(),'ai-relay-test-')));t.after(()=>rm(dir,{recursive:true,force:true}));const project=path.join(dir,'project');await mkdir(project);return {dir,projects:{demo:project},journal:path.join(dir,'journal.json')};}
const run={id:'fixture-run',project:'demo',role:'execute',model:'explicit-fixture-model',effort:'medium',prompt:'Synthetic fixture',prior:[],feedback:[]};
test('Protocol simulation, never a real Codex proof: claim, heartbeat, completion',async t=>{const f=await fixture(t);const calls=[];const transport=async p=>{calls.push(p);return p.action==='claim'?run:{status:'running'};};const client={run:async options=>{assert.equal(options.model,run.model);options.onStarted({threadId:'synthetic-thread',turnId:'synthetic-turn'});return {status:'completed',output:'synthetic result'};}};assert.equal((await runOnce({...f,transport,client})).status,'completed');assert.equal(calls.filter(p=>p.action==='finish').length,1);assert.equal(JSON.parse(await readFile(f.journal)).phase,'delivered');});
test('Delivery replay does not execute Codex twice',async t=>{const f=await fixture(t);await writeFile(f.journal,JSON.stringify({phase:'pending_delivery',run_id:run.id,result:{status:'completed',output:'synthetic'}}));const calls=[];await runOnce({...f,transport:async p=>calls.push(p),client:{run:()=>assert.fail('must not execute')}});assert.equal(calls[0].action,'finish');});
test('Uncertain crash blocks replay',async t=>{const f=await fixture(t);await writeFile(f.journal,'{"phase":"running","run_id":"fixture"}');await assert.rejects(runOnce({...f,transport:()=>assert.fail('must not claim'),client:{}}),/uncertain/);});
for(const scenario of ['cancel_requested','offline','signal'])test('Interruption DURING execution: '+scenario,async t=>{
 const f=await fixture(t);const sent=[];let executing=false,aborted=false,beats=0,executions=0;
 const controller=new AbortController();
 const transport=async p=>{
  sent.push(p);
  if(p.action==='claim')return run;
  if(p.action==='heartbeat') {
   beats++;
   if(beats===1)return {status:'running'};
   assert.equal(executing,true,'fault must occur after client.run began');
   if(scenario==='offline')throw new Error('offline during execution');
   if(scenario==='signal')controller.abort();
   return {status:scenario==='cancel_requested'?'cancel_requested':'running'};
  }
 };
 const client={run:async({signal})=>{
  executions++;executing=true;assert.equal(signal.aborted,false);
  return new Promise(resolve=>signal.addEventListener('abort',()=>{aborted=true;resolve({status:'interrupted',output:''});},{once:true}));
 }};
 const execution=runOnce({...f,transport,client,signal:controller.signal,heartbeatMs:5});
 if(scenario==='offline')await assert.rejects(execution,/reconciliation/);
 else assert.equal((await execution).status,'interrupted');
 assert.equal(executions,1);assert.equal(aborted,true);assert.ok(beats>=2);
 assert.equal(sent.filter(p=>p.action==='claim').length,1);
 assert.equal(sent.filter(p=>p.action==='finish').length,scenario==='offline'?0:1);
 const state=JSON.parse(await readFile(f.journal));
 assert.equal(state.phase,scenario==='offline'?'needs_reconciliation':'delivered');
 if(scenario==='offline')await assert.rejects(runOnce({...f,transport:()=>assert.fail('no replay'),client}),/uncertain/);
 else assert.equal(sent.at(-1).result.status,'interrupted');
});
test('Only allowlisted real project and approved HTTPS destination',async t=>{const f=await fixture(t);await assert.rejects(projectDirectory(f.projects,'other'),/allowlisted/);const link=path.join(f.dir,'link');await symlink(f.projects.demo,link);await assert.rejects(projectDirectory({demo:link},'demo'),/Symlink/);assert.throws(()=>httpTransport('https://evil.invalid/api/ai-relay','fixture'),/explicit/);assert.throws(()=>httpTransport('https://ai.oling.fr/api/ai-relay',''),/provisioned/);});
test('Review role is separate and requires evidence',()=>{assert.match(buildPrompt({...run,role:'review'}),/Independent review/);assert.match(buildPrompt({...run,role:'review'}),/read-only/);});

test('Already aborted and initialization-time abort never claim or execute',async t=>{
 const f=await fixture(t);const c=new AbortController();c.abort();const transport=()=>assert.fail('must not contact relay');const client={initialize:()=>assert.fail('must not initialize'),run:()=>assert.fail('must not execute')};
 assert.equal((await runOnce({...f,client,transport,signal:c.signal})).status,'aborted');assert.equal((await initializeAndRun({...f,client,transport,signal:c.signal})).status,'aborted');
 const during=new AbortController();assert.equal((await initializeAndRun({...f,transport,signal:during.signal,client:{initialize:async()=>{during.abort();},run:()=>assert.fail('must not execute')}})).status,'aborted');
});
test('Expired heartbeat without another claim stops work and blocks any delivery/re-execution',async t=>{
 const f=await fixture(t);let beats=0;let executions=0;const calls=[];
 const transport=async p=>{calls.push(p.action);if(p.action==='claim')return run;if(p.action==='heartbeat')return {status:++beats===1?'running':'uncertain'};assert.fail('must not deliver expired run');};
 const client={run:async({signal})=>{executions++;return new Promise(resolve=>signal.addEventListener('abort',()=>resolve({status:'interrupted'}),{once:true}));}};
 await assert.rejects(runOnce({...f,transport,client,heartbeatMs:5}),/reconciliation/);assert.equal(executions,1);assert.equal(calls.filter(x=>x==='claim').length,1);assert.equal(JSON.parse(await readFile(f.journal)).phase,'needs_reconciliation');
 await assert.rejects(runOnce({...f,transport:()=>assert.fail('no further request'),client}),/uncertain/);
});
test('Expired claim is detected before any model turn',async t=>{const f=await fixture(t);await assert.rejects(runOnce({...f,client:{run:()=>assert.fail('must not execute')},transport:async p=>p.action==='claim'?run:{status:'uncertain'}}),/reconciliation/);});
test('Conflicting pending delivery becomes terminal reconciliation, never endless replay',async t=>{const f=await fixture(t);await writeFile(f.journal,JSON.stringify({phase:'pending_delivery',run_id:run.id,result:{status:'completed',output:'synthetic'}}));let attempts=0;const transport=async()=>{attempts++;throw new RelayConflict('expired');};const client={run:()=>assert.fail('must not execute')};await assert.rejects(runOnce({...f,client,transport}),/expired/);assert.equal(JSON.parse(await readFile(f.journal)).phase,'needs_reconciliation');await assert.rejects(runOnce({...f,client,transport}),/uncertain/);assert.equal(attempts,1);});

test('Production relay refuses before reading config, credentials or making a claim',async()=>{
 await assert.rejects(main('/nonexistent-must-not-read.json'),/Real Codex execution blocked/);
});
