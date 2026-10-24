import {spawn} from 'node:child_process';
import {createInterface} from 'node:readline';
import {EventEmitter} from 'node:events';
import path from 'node:path';

const SAFE_PATH='/opt/homebrew/bin:/usr/local/bin:/usr/bin:/bin:/usr/sbin:/sbin';
// No configuration/env flag can certify runtime isolation. Reopen only after a reviewed
// permission-profile implementation and an explicitly authorized synthetic OS recipe.
export function assertRuntimeReady() {
  throw new Error('Real Codex execution blocked: project-only reads and authentication isolation are not certified; tool-free probe also pending approval and certification');
}
/** Preserve runtime location, never transport/provider credentials or shell injection variables. */
export function childEnvironment(source=process.env) {
  const env={PATH:SAFE_PATH,LANG:'en_US.UTF-8'};
  for(const key of ['HOME','CODEX_HOME','TMPDIR'])if(source[key]&&path.isAbsolute(source[key]))env[key]=source[key];
  return env;
}
export function runtimeOverrides({noTools=false}={}) {
  return {
    web_search:'disabled',allow_login_shell:false,
    'features.apps':false,'features.plugins':false,'features.hooks':false,
    'features.browser_use':false,'features.in_app_browser':false,'features.computer_use':false,
    'features.multi_agent':false,'agents.enabled':false,'features.shell_snapshot':false,
    'features.code_mode.enabled':false,'features.shell_tool':!noTools,
    'tools.view_image':false,'mcp_servers':{},
    'shell_environment_policy.inherit':'none','shell_environment_policy.set':{PATH:SAFE_PATH,LANG:'en_US.UTF-8'},
    'sandbox_workspace_write.network_access':false,
    'sandbox_workspace_write.exclude_slash_tmp':true,'sandbox_workspace_write.exclude_tmpdir_env_var':true,
  };
}
function toml(value) {
  if(typeof value==='object')return '{'+Object.entries(value).map(([k,v])=>JSON.stringify(k)+'='+toml(v)).join(',')+'}';
  return JSON.stringify(value);
}
export function sandboxPolicy(cwd,readOnly) {
  return readOnly?{type:'readOnly',networkAccess:false}:{type:'workspaceWrite',writableRoots:[cwd],networkAccess:false,excludeSlashTmp:true,excludeTmpdirEnvVar:true};
}
function checkSandbox(actual,cwd,readOnly) {
  const expected=sandboxPolicy(cwd,readOnly);
  if(!actual||actual.type!==expected.type||actual.networkAccess!==false)throw new Error('Effective Codex sandbox does not match restrictions');
  if(!readOnly&&(!actual.excludeSlashTmp||!actual.excludeTmpdirEnvVar||(actual.writableRoots??[]).some(root=>path.resolve(root)!==cwd)))throw new Error('Unexpected Codex writable roots');
}

/** Official stdio protocol only. No auth-file reads, exports or persistent config changes. */
export class AppServerProtocol extends EventEmitter {
  // Protocol harness; production entry points must use the gated CodexClient below.
  constructor(binary,cwd,{noTools=false,interruptTimeoutMs=10000,terminationGraceMs=1000,terminationKillMs=2000}={}) {
    super();this.sequence=0;this.pending=new Map();this.noTools=noTools;this.interruptTimeoutMs=interruptTimeoutMs;
    this.terminationGraceMs=terminationGraceMs;this.terminationKillMs=terminationKillMs;
    const overrides=runtimeOverrides({noTools});
    const args=['app-server','--listen','stdio://','--strict-config',...Object.entries(overrides).flatMap(([k,v])=>['-c',k+'='+toml(v)])];
    // New POSIX group belongs exclusively to this spawn, never the caller's group.
    this.ownsGroup=process.platform!=='win32';
    this.process=spawn(binary,args,{cwd,env:childEnvironment(),stdio:['pipe','pipe','pipe'],detached:this.ownsGroup});
    this.exited=false;
    this.process.once('exit',()=>{this.exited=true;});
    this.process.once('error',()=>{if(!this.process.pid)this.exited=true;});
    this.process.stderr.resume(); // Never persist raw runtime/provider diagnostics.
    createInterface({input:this.process.stdout}).on('line',line=>{
      let message;try {message=JSON.parse(line);}catch{return;}
      if(message.id!==undefined&&!message.method) {
        const request=this.pending.get(message.id);if(!request)return;
        this.pending.delete(message.id);clearTimeout(request.timer);
        message.error?request.reject(new Error(`RPC ${request.method}: ${message.error.code}`)):request.resolve(message.result);
      } else if(message.id!==undefined) {
        // Interrupt is emitted before decline, so the client never authorizes continuation.
        this.emit('approval',{method:message.method,id:message.id,threadId:message.params?.threadId,turnId:message.params?.turnId});
        if(message.method.endsWith('/requestApproval'))this.send({id:message.id,result:{decision:'decline'}});
        else this.send({id:message.id,error:{code:-32601,message:'Human intervention required; unsupported request'}});
      } else this.emit('notification',message);
    });
    const failed=()=>{for(const r of this.pending.values()){clearTimeout(r.timer);r.reject(new Error('Codex process unavailable'));}this.pending.clear();this.emit('offline');};
    this.process.stdin.on('error',failed);this.process.on('error',failed);this.process.on('exit',failed);
  }
  send(message){if(!this.process.stdin.destroyed&&!this.process.stdin.writableEnded)this.process.stdin.write(JSON.stringify(message)+'\n');}
  rpc(method,params={}) {
    return new Promise((resolve,reject)=>{
      const id=++this.sequence;const timer=setTimeout(()=>{this.pending.delete(id);reject(new Error(`RPC timeout: ${method}`));},30000);
      this.pending.set(id,{resolve,reject,timer,method});this.send({id,method,params});
    });
  }
  async initialize() {
    await this.rpc('initialize',{clientInfo:{name:'oling_ai_local',title:'OLING AI local relay',version:'0.2.0'},capabilities:{experimentalApi:true}});
    this.send({method:'initialized',params:{}});
    const auth=await this.rpc('account/read',{refreshToken:false});
    if(auth.account?.type!=='chatgpt')throw new Error('Existing managed ChatGPT login required; no API-key fallback');
    // Public capability metadata only; never config/read, which may expose configured secrets.
    let cursor=null;
    do {const status=await this.rpc('mcpServerStatus/list',{cursor,limit:100});if(status.data?.length)throw new Error('External MCP capabilities still present; refuse to start a turn');cursor=status.nextCursor;}while(cursor);
    return {authType:auth.account.type};
  }
  async run({cwd,prompt,model,effort='medium',readOnly=true,signal,onStarted=()=>{},timeoutMs=120000}) {
    if(signal?.aborted)return {status:'interrupted',output:'',approval:false,evidence:[]};
    cwd=path.resolve(cwd);
    const thread=await this.rpc('thread/start',{cwd,model,ephemeral:true,sandbox:readOnly?'read-only':'workspace-write',approvalPolicy:'on-request',approvalsReviewer:'user',config:runtimeOverrides({noTools:this.noTools})});
    if(thread.model!==model)throw new Error('Codex changed the requested model; stopped');
    checkSandbox(thread.sandbox,cwd,readOnly);
    if(thread.approvalPolicy!=='on-request'||thread.approvalsReviewer!=='user')throw new Error('Effective approval routing does not match restrictions');
    if(signal?.aborted)return {threadId:thread.thread.id,status:'interrupted',output:'',approval:false,evidence:[]};
    const threadId=thread.thread.id;
    return new Promise((resolve,reject)=>{
      let turnId=null,output='',approval=false,settled=false,stopRequested=false,interruptSent=false,interruptAcknowledged=false,terminal=null,interruptTimer;
      const evidence=[];
      const cleanup=()=>{clearTimeout(timer);clearTimeout(interruptTimer);this.off('notification',listener);this.off('approval',denied);this.off('offline',offline);signal?.removeEventListener('abort',abort);};
      const finish=(value,error)=>{if(settled)return;settled=true;cleanup();error?reject(error):resolve(value);};
      const failClosed=reason=>{
        if(settled)return;settled=true;cleanup();
        // Do not report a stopped turn while a process/group could still be running.
        this.close().then(()=>reject(new Error(reason+'; outcome uncertain, reconciliation required')),
          error=>reject(new Error(reason+'; termination unconfirmed: '+error.message+'; reconciliation required')));
      };
      const complete=()=>{
        if(!terminal||(stopRequested&&!interruptAcknowledged))return;
        finish({threadId,turnId:terminal.id,status:approval?'denied':stopRequested?'interrupted':terminal.status,output,approval,model,effort,evidence});
      };
      const interrupt=()=>{
        stopRequested=true;
        interruptTimer??=setTimeout(()=>failClosed('Interruption not acknowledged'),this.interruptTimeoutMs);
        if(!turnId||interruptSent)return;interruptSent=true;
        this.rpc('turn/interrupt',{threadId,turnId}).then(()=>{interruptAcknowledged=true;complete();}).catch(()=>failClosed('Interruption rejected'));
      };
      const abort=()=>interrupt();
      const denied=request=>{if(request.threadId&&request.threadId!==threadId)return;approval=true;turnId??=request.turnId;interrupt();};
      const offline=()=>finish(null,new Error('Codex disconnected; outcome uncertain'));
      const listener=message=>{
        if(message.params?.threadId!==threadId)return;
        if(message.method==='item/started'&&stopRequested&&!['agentMessage','reasoning'].includes(message.params.item?.type))return failClosed('Tool activity after refusal or interruption');
        if(message.method==='item/started'&&this.noTools&&!['agentMessage','reasoning','userMessage'].includes(message.params.item?.type))return failClosed('Unexpected tool activity in restricted probe');
        if(message.method==='item/completed') {
          const item=message.params.item;
          if(item?.type==='agentMessage'&&!stopRequested)output=item.text;
          else if(item&&evidence.length<100)evidence.push({id:item.id,type:item.type,status:item.status??null,exitCode:item.exitCode??null});
        }
        if(message.method==='turn/completed'){terminal=message.params.turn;turnId??=terminal.id;complete();}
      };
      const timer=setTimeout(()=>{interrupt();},timeoutMs);
      this.on('notification',listener);this.on('approval',denied);this.on('offline',offline);signal?.addEventListener('abort',abort,{once:true});
      if(signal?.aborted){finish({threadId,status:'interrupted',output:'',approval:false,evidence:[]});return;}
      this.rpc('turn/start',{threadId,model,effort,approvalPolicy:'on-request',approvalsReviewer:'user',sandboxPolicy:sandboxPolicy(cwd,readOnly),input:[{type:'text',text:prompt}]}).then(r=>{turnId=r.turn.id;onStarted({threadId,turnId});if(stopRequested||signal?.aborted)interrupt();}).catch(e=>finish(null,e));
    });
  }
  close() {
    this.closing??=this.terminateOwnedGroup();return this.closing;
  }
  async terminateOwnedGroup() {
    const child=this.process;
    const groupAlive=()=>{
      if(!child.pid)return false;
      if(!this.ownsGroup)return !this.exited;
      try {process.kill(-child.pid,0);return true;}catch(error){if(error.code==='ESRCH')return false;throw error;}
    };
    const signal=kind=>{
      if(!child.pid)return;
      try {if(this.ownsGroup)process.kill(-child.pid,kind);else if(!this.exited)child.kill(kind);}
      catch(error){if(error.code!=='ESRCH')throw error;}
    };
    const wait=async ms=>{
      const deadline=Date.now()+ms;
      do {if(this.exited&&!groupAlive())return true;await new Promise(resolve=>setTimeout(resolve,10));}while(Date.now()<deadline);
      return this.exited&&!groupAlive();
    };
    if(!child.stdin.destroyed&&!child.stdin.writableEnded)child.stdin.end();
    signal('SIGTERM');
    let escalated=false;
    if(!await wait(this.terminationGraceMs)) {
      escalated=true;signal('SIGKILL');
      if(!await wait(this.terminationKillMs))throw new Error('Owned process/group exit not confirmed before deadline');
    }
    // Unsupported platforms must not silently claim descendants have stopped.
    if(!this.ownsGroup)throw new Error('Direct process exited; descendant termination unsupported on this platform');
    return {exited:true,groupExited:true,escalated};
  }
}

export class CodexClient extends AppServerProtocol {
  constructor(binary,cwd,options={}) {
    assertRuntimeReady(); // Before spawn, including noTools=true; no credential/config read.
    super(binary,cwd,options);
  }
}
