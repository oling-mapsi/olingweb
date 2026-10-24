import {CodexClient,assertRuntimeReady} from './app-server.mjs';
import {mkdir,writeFile} from 'node:fs/promises';
import path from 'node:path';
assertRuntimeReady(); // Remains blocked pending explicit approval and runtime certification.
const binary=process.env.CODEX_BIN;
if(!binary) throw new Error('Set CODEX_BIN to an existing official Codex executable.');
const cwd=path.resolve('var/ai-fixture');await mkdir(cwd,{recursive:true});
const client=new CodexClient(binary,cwd,{noTools:true});
try {
 const auth=await client.initialize();
 const models=await client.rpc('model/list',{});
 const selected=models.data.find(m=>m.isDefault)??models.data[0];
 const model=process.env.AI_MODEL??selected.model;
 const effort='low';
 console.log(JSON.stringify({phase:'authenticated',...auth,model,effort}));
 const result=await client.run({cwd,model,effort,prompt:'Local integration probe only. Do not use any tools, read files, access network or modify anything. Reply exactly OLING_AI_ROUNDTRIP_42.',readOnly:true});
 const evidence={at:new Date().toISOString(),...auth,...result,expected:result.output.trim()==='OLING_AI_ROUNDTRIP_42',toolFreeObserved:result.evidence.length===0,noToolsEnforcement:'requested-not-yet-certified-on-real-runtime'};
 await writeFile('docs/ai/probe-result.json',JSON.stringify(evidence,null,2)+'\n');
 console.log(JSON.stringify(evidence));
 if(result.status!=='completed'||!evidence.expected)process.exitCode=1;
} finally {await client.close();}
