import test from 'node:test';
import assert from 'node:assert/strict';
import {mkdtemp,writeFile,chmod,rm} from 'node:fs/promises';
import {once} from 'node:events';
import os from 'node:os';
import path from 'node:path';
import {AppServerProtocol} from './app-server.mjs';

// Only synthetic processes created here. No Codex binary, credentials or network.
async function stubbornPeer(t,{descendant=false,graceful=false}={}) {
  const dir=await mkdtemp(path.join(os.tmpdir(),'ai-stop-fixture-'));
  t.after(()=>rm(dir,{recursive:true,force:true}));
  const file=path.join(dir,'peer');
  await writeFile(file,`#!/usr/bin/env node
const send=pid=>process.stdout.write(JSON.stringify({method:'fixture/ready',params:{pid}})+'\\n');
process.on('SIGTERM',()=>{${graceful?'process.exit(0);':''}});
setInterval(()=>{},1000);
${descendant?`import('node:child_process').then(({spawn})=>{
 const child=spawn(process.execPath,['-e',"process.on('SIGTERM',()=>{});setInterval(()=>{},1000);process.send('ready');"],{stdio:['ignore','ignore','ignore','ipc']});
 child.once('message',()=>send(child.pid));
});`:'send(process.pid);'}
`);
  await chmod(file,0o700);
  const peer=new AppServerProtocol(file,dir,{terminationGraceMs:80,terminationKillMs:1500});
  t.after(()=>peer.close());
  const [message]=await once(peer,'notification',{signal:AbortSignal.timeout(3000)});
  return {peer,descendantPid:message.params.pid};
}
function alive(pid) {try {process.kill(pid,0);return true;}catch(error){if(error.code==='ESRCH')return false;throw error;}}

test('Termination awaits real exit and escalates stubborn owned parent and child only',async t=>{
  const foreign=await stubbornPeer(t,{graceful:true});
  const {peer,descendantPid}=await stubbornPeer(t,{descendant:true});
  assert.equal(alive(descendantPid),true);
  const started=Date.now();const closing=peer.close();assert.equal(peer.close(),closing);
  const result=await closing;
  assert.deepEqual(result,{exited:true,groupExited:true,escalated:true});
  assert.equal(peer.exited,true);assert.ok(peer.process.signalCode||peer.process.exitCode!==null);
  assert.equal(alive(descendantPid),false);assert.equal(alive(peer.process.pid),false);
  assert.equal(alive(foreign.peer.process.pid),true,'unrelated group must survive');
  assert.ok(Date.now()-started<2500,'termination must be bounded');
});
test('Cooperative owned process exits without escalation',async t=>{
  const {peer}=await stubbornPeer(t,{graceful:true});
  assert.deepEqual(await peer.close(),{exited:true,groupExited:true,escalated:false});
});
