import fcntl,hashlib,json,os,shutil,subprocess,sys,tarfile
from pathlib import Path
phase,package,commit=sys.argv[1:]
assert len(commit)==40 and all(c in '0123456789abcdef' for c in commit)
lock=open('/var/lock/oling-deploy-cms-p0.lock','a');fcntl.flock(lock,fcntl.LOCK_EX|fcntl.LOCK_NB)
link=Path('/var/www/oling-current');old=link.resolve();name='oling-ai-track-'+commit[:12]
release=Path('/var/www/releases')/name;backup=Path('/var/backups/oling')/name
command='src/Command/AiTrackCommand.php';registry='src/Service/Ai/AiNativeRegistry.php'
expected='13733009e1fa13357faaef00d94225e016537e5c42b14ed19508c3f3ea683237'

def run(a,**kw):return subprocess.run(a,check=True,**kw)
def digest(p):return hashlib.sha256(Path(p).read_bytes()).hexdigest()
def console(*args):return run(['docker','exec','--user','www-data','--workdir',str(release),'-e','APP_ENV=prod','-e','APP_DEBUG=0','oling-php84','php','bin/console',*args])
def hashes(root):return {str(p.relative_to(root)):digest(p) for folder in ['src','templates','config','public/crm'] for p in (root/folder).rglob('*') if p.is_file()}
def switch(target):
 tmp=Path('/var/www/oling-current-ai-track-next');assert not tmp.exists() and not tmp.is_symlink();tmp.symlink_to(target);os.replace(tmp,link)
 run(['docker','kill','--signal=USR2','oling-php84'],stdout=subprocess.DEVNULL);run(['systemctl','reload','apache2'])

if phase=='prepare':
 assert old.name=='oling-crm-7a86077d0673','Active release changed; inspect before proceeding'
 assert not release.exists() and not backup.exists() and not (old/command).exists()
 assert digest(old/registry)==expected
 assert (old/'var/ai-private').resolve()==Path('/var/www/ai-private')
 assert shutil.disk_usage('/var/www').free>2*1024**3
 before=hashes(old)
 # Code-only publication: preserve all database/assets in place, no migrations or data operations.
 backup.mkdir(mode=0o700);(backup/'previous-release.txt').write_text(str(old))
 shutil.copy2(old/registry,backup/'AiNativeRegistry.php');assert digest(backup/'AiNativeRegistry.php')==expected
 (backup/'command-was-absent').write_text(command)
 (backup/'before-hashes.json').write_text(json.dumps(before,indent=2))
 release.mkdir();run(['rsync','-a','--exclude=var/cache/','--exclude=var/log/','--exclude=var/sessions/','--exclude=.git/','--exclude=node_modules/',str(old)+'/',str(release)+'/'])
 with tarfile.open(package) as t:
  members=t.getmembers();assert len(members)==2 and {m.name for m in members}=={command,registry}
  assert all(m.isfile() for m in members)
  t.extractall(release,members=members,filter='data')
 after=hashes(release)
 for f,h in before.items():
  if f!=registry:assert after[f]==h,f
 assert (release/'var/ai-private').resolve()==(old/'var/ai-private').resolve()
 for p in [release/'var/cache',release/'var/log']:
  p.mkdir(parents=True,exist_ok=True);run(['chown','-R','www-data:www-data',str(p)]);run(['chmod','-R','g+rwX',str(p)])
 for f in [command,registry]:run(['docker','exec','--workdir',str(release),'oling-php84','php','-l',f])
 console('cache:clear','--no-debug');console('lint:container','--no-debug');console('app:ai:track','--help','--no-debug')
 manifest={'commit':commit,'previous':str(old),'release':str(release),'package_sha256':digest(package),'files':{f:digest(release/f) for f in [command,registry]},'previous_registry_sha256':expected,'unchanged_existing_files':len(before)-1,'database_or_config_changes':False,'backup_verified':True}
 (backup/'manifest.json').write_text(json.dumps(manifest,indent=2));(release/'AI_TRACK_RELEASE.json').write_text(json.dumps(manifest,indent=2))
 print(json.dumps({'prepared':str(release),'backup':str(backup),'unchanged_existing_files':len(before)-1}),flush=True)
elif phase=='activate':
 m=json.loads((backup/'manifest.json').read_text());assert str(old)==m['previous']
 assert digest(package)==m['package_sha256']
 for f,h in m['files'].items():assert digest(release/f)==h
 assert digest(backup/'AiNativeRegistry.php')==m['previous_registry_sha256']
 before=json.loads((backup/'before-hashes.json').read_text())
 for f,h in before.items():assert digest(old/f)==h,f
 run(['apache2ctl','configtest']);switch(release)
 try:
  for host,path,expected_status in [('oling.fr','/',200),('oling.fr','/login',200),('crm.oling.fr','/crm/login',200),('crm.oling.fr','/admin/crm',302),('ai.oling.fr','/ai/login',200),('ai.oling.fr','/admin/ai',302),('ai.oling.fr','/api/ai/mcp',503),('oling.fr','/admin/ai',404),('ai.oling.fr','/admin/crm',404)]:
   code=subprocess.check_output(['curl','--noproxy','*','--resolve',host+':443:127.0.0.1','-sS','-o','/dev/null','-w','%{http_code}','https://'+host+path],text=True);assert int(code)==expected_status,(host,path,code)
  console('app:ai:track','--help','--no-debug');run(['systemctl','is-active','apache2','mysql'])
 except BaseException:switch(old);raise
 (backup/'activated').write_text(commit);print(json.dumps({'deployed':str(release),'http_smoke':'PASS','command_available':True}),flush=True)
else:raise SystemExit('Use prepare or activate')
