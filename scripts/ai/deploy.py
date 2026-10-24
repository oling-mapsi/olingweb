#!/usr/bin/env python3
"""Server-only, explicit AI overlay. No DNS, OAuth provisioning or autonomous services."""
import fcntl,hashlib,json,os,shutil,subprocess,sys,tarfile,textwrap
from pathlib import Path
phase,package,commit=sys.argv[1:]
assert len(commit)==40 and all(c in '0123456789abcdef' for c in commit)
lock=open('/var/lock/oling-deploy-cms-p0.lock','a');fcntl.flock(lock,fcntl.LOCK_EX|fcntl.LOCK_NB)
link=Path('/var/www/oling-current');old=link.resolve();name='oling-ai-'+commit[:12]
release=Path('/var/www/releases')/name;backup=Path('/var/backups/oling')/name
storage=Path('/var/www/ai-private')

def run(args,**kw):return subprocess.run(args,check=True,**kw)
def digest(p):
 h=hashlib.sha256()
 with open(p,'rb') as f:
  for b in iter(lambda:f.read(1048576),b''):h.update(b)
 return h.hexdigest()
def console(*args):return run(['docker','exec','--user','www-data','--workdir',str(release),'-e','APP_ENV=prod','-e','APP_DEBUG=0','oling-php84','php','bin/console',*args])
def switch(target):
 tmp=Path('/var/www/oling-current-ai-next');assert not tmp.exists() and not tmp.is_symlink();tmp.symlink_to(target);os.replace(tmp,link)
 run(['docker','kill','--signal=USR2','oling-php84'],stdout=subprocess.DEVNULL);run(['systemctl','reload','apache2'])
def allowed(p):return p.startswith(('src/Controller/Ai/','src/Service/Ai/','templates/ai_control/')) or p in ['migrations/Version20261024180000.php','migrations/Version20261024220000.php','scripts/ai/patch-security.py','scripts/ai/ai-firewalls.yaml']
def hashes(root):
 return {str(p.relative_to(root)):digest(p) for folder in ['src','templates','config'] for p in (root/folder).rglob('*') if p.is_file()}

if phase=='prepare':
 assert old.name=='oling-crm-876770b48e09', 'Active release changed; stop for rebase/review'
 assert old.is_dir() and link.is_symlink() and not release.exists() and not backup.exists()
 assert shutil.disk_usage('/var/www').free>4*1024**3
 assert not storage.exists(), 'Existing AI storage must be reviewed'
 run(['systemctl','is-active','apache2','mysql'])
 before=hashes(old)
 backup.mkdir(mode=0o700);(backup/'previous-release.txt').write_text(str(old));os.chmod(backup,0o700)
 # Backup remains root-only. No database rows or credentials are printed or exported to the client.
 dump=backup/'database.sql';key=backup/'backup.key';key.write_bytes(os.urandom(64).hex().encode());key.chmod(0o600)
 with dump.open('wb') as f:run(['mysqldump','--single-transaction','--routines','--triggers','--events','oling_db'],stdout=f)
 dump.chmod(0o600)
 restore='oling_ai_restore_'+commit[:12]
 run(['mysql','-e','CREATE DATABASE '+restore+' CHARACTER SET utf8mb4'])
 try:
  with dump.open('rb') as f:run(['mysql',restore],stdin=f)
  result=subprocess.check_output(['mysql','-N','-e',"SELECT table_schema,COUNT(*) FROM information_schema.tables WHERE table_schema IN ('oling_db','"+restore+"') GROUP BY table_schema"],text=True)
  vals=[int(l.split('\t')[1]) for l in result.strip().splitlines()];assert len(vals)==2 and vals[0]==vals[1]
  (backup/'restore-proof.txt').write_text(result)
 finally:run(['mysql','-e','DROP DATABASE '+restore])
 snapshot=backup/'snapshot.tar.gz'
 with snapshot.open('wb') as f:run(['tar','--exclude=var/cache','--exclude=var/log','--exclude=.git','--exclude=node_modules','-czhf','-','-C','/',str(old).lstrip('/'),'etc/apache2',str(dump).lstrip('/')],stdout=f,stderr=subprocess.DEVNULL)
 encrypted=backup/'snapshot.enc'
 run(['openssl','enc','-aes-256-cbc','-salt','-pbkdf2','-iter','200000','-in',str(snapshot),'-out',str(encrypted),'-pass','file:'+str(key)])
 encrypted.chmod(0o600)
 # Verify complete decrypt/byte equivalence without showing contents.
 verifier=backup/'verify.tar.gz'
 run(['openssl','enc','-d','-aes-256-cbc','-pbkdf2','-iter','200000','-in',str(encrypted),'-out',str(verifier),'-pass','file:'+str(key)])
 assert digest(snapshot)==digest(verifier)
 for p in [snapshot,verifier,dump]:p.unlink()
 (backup/'snapshot.sha256').write_text(digest(encrypted))
 release.mkdir();run(['rsync','-a','--exclude=var/cache/','--exclude=var/log/','--exclude=.git/','--exclude=node_modules/',str(old)+'/',str(release)+'/'])
 with tarfile.open(package) as t:
  members=[m for m in t.getmembers() if not m.isdir()]
  assert members and all(m.isfile() and allowed(m.name) and not m.name.startswith('/') and '..' not in Path(m.name).parts for m in members)
  assert all(not (release/m.name).exists() for m in members), 'AI file collision'
  t.extractall(release,members=members,filter='data')
 run(['python3',str(release/'scripts/ai/patch-security.py'),str(release/'config/packages/security.yaml')])
 after=hashes(release)
 for p,h in before.items():
  if p!='config/packages/security.yaml':assert after[p]==h,p
 sec=(release/'config/packages/security.yaml').read_text();block=textwrap.indent((release/'scripts/ai/ai-firewalls.yaml').read_text(),'        ')
 assert sec.replace(block,'',1)==(old/'config/packages/security.yaml').read_text()
 storage.mkdir(mode=0o700);run(['chown','www-data:www-data',str(storage)]);(release/'var/ai-private').symlink_to(storage)
 for p in [release/'var/cache',release/'var/log']:
  p.mkdir(parents=True,exist_ok=True);run(['chown','-R','www-data:www-data',str(p)]);run(['chmod','-R','g+rwX',str(p)])
 # No global migrate and no schema:update; only these two additive versions.
 for version in ['20261024180000','20261024220000']:console('doctrine:migrations:execute','DoctrineMigrations\\Version'+version,'--up','--no-interaction')
 console('cache:clear','--no-debug');console('lint:container','--no-debug');console('lint:twig','templates/ai_control','--no-debug')
 manifest={'commit':commit,'previous':str(old),'release':str(release),'package_sha256':digest(package),'backup_sha256':digest(encrypted),'migration_versions':['20261024180000','20261024220000'],'unchanged_existing_code':len(before)-1,'files':{m.name:digest(release/m.name) for m in members}}
 manifest['files']['config/packages/security.yaml']=digest(release/'config/packages/security.yaml')
 (backup/'manifest.json').write_text(json.dumps(manifest,indent=2));(release/'AI_RELEASE.json').write_text(json.dumps(manifest,indent=2))
 print(json.dumps({'prepared':str(release),'backup':str(backup),'backup_verified':True,'commit':commit}),flush=True)
elif phase=='activate':
 m=json.loads((backup/'manifest.json').read_text());assert str(old)==m['previous']
 assert digest(package)==m['package_sha256'] and digest(backup/'snapshot.enc')==m['backup_sha256']
 for f,h in m['files'].items():assert digest(release/f)==h,f
 run(['apache2ctl','configtest']);switch(release)
 try:
  for host,path,expected in [('oling.fr','/',200),('oling.fr','/login',200),('crm.oling.fr','/crm/login',200),('crm.oling.fr','/admin/crm',302),('ai.oling.fr','/ai/login',200),('ai.oling.fr','/admin/ai',302),('ai.oling.fr','/api/ai/mcp',503),('oling.fr','/admin/ai',404),('ai.oling.fr','/admin/crm',404)]:
   code=subprocess.check_output(['curl','--noproxy','*','--resolve',host+':443:127.0.0.1','-sS','-o','/dev/null','-w','%{http_code}','https://'+host+path],text=True);assert int(code)==expected,(host,path,code)
  run(['systemctl','is-active','apache2','mysql'])
 except BaseException:switch(old);raise
 (backup/'activated').write_text(commit);print(json.dumps({'deployed':str(release),'commit':commit,'http_smoke':'PASS'}),flush=True)
else:raise SystemExit('Use prepare or activate')
