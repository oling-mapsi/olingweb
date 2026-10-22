#!/usr/bin/env python3
"""Executed as root ON SERVER. prepare and activate explicitly separated for off-host backup."""
import fcntl,hashlib,json,os,shutil,subprocess,sys,tarfile
from pathlib import Path
phase,package,commit=sys.argv[1:]
assert len(commit)==40 and all(x in '0123456789abcdef' for x in commit)
lock=open('/var/lock/oling-deploy-cms-p0.lock','a');fcntl.flock(lock,fcntl.LOCK_EX|fcntl.LOCK_NB)
link=Path('/var/www/oling-current'); old=link.resolve(); name='oling-crm-'+commit[:12]
release=Path('/var/www/releases')/name; backup=Path('/var/backups/oling')/name

def run(args,**kw): return subprocess.run(args,check=True,**kw)
def console(*args): return run(['sudo','-u','www-data','env','APP_ENV=prod','APP_DEBUG=0','php','bin/console',*args],cwd=release)
def digest(path):return hashlib.sha256(path.read_bytes()).hexdigest()
def switch(target):
 tmp=Path('/var/www/oling-current-crm-next');assert not tmp.exists() and not tmp.is_symlink();tmp.symlink_to(target);os.replace(tmp,link);run(['systemctl','reload','apache2'])

if phase=='prepare':
 prior=json.loads((old/'CRM_RELEASE.json').read_text()) if (old/'CRM_RELEASE.json').exists() else None
 assert old.is_dir() and link.is_symlink() and not release.exists() and not backup.exists()
 assert shutil.disk_usage('/var/www').free>2*1024**3
 run(['systemctl','is-active','apache2','mysql'])
 backup.mkdir(mode=0o700);(backup/'previous-release.txt').write_text(str(old))
 key=backup/'backup.key';key.write_bytes(os.urandom(64).hex().encode());key.chmod(0o600)
 # Freeze a consistent database export; code/media remain protected by deployment lock.
 with (backup/'database.sql').open('wb') as f:run(['mysqldump','--single-transaction','--routines','--triggers','--events','oling_db'],stdout=f)
 # Validate restore on the server, never against development or production tables.
 restore='oling_crm_restore_'+commit[:12]
 run(['mysql','-e','CREATE DATABASE '+restore+' CHARACTER SET utf8mb4'])
 try:
  with (backup/'database.sql').open('rb') as f:run(['mysql',restore],stdin=f)
  counts=subprocess.check_output(['mysql','-N','-e',"SELECT table_schema,COUNT(*) FROM information_schema.tables WHERE table_schema IN ('oling_db','"+restore+"') GROUP BY table_schema"],text=True)
  values=[int(l.split('\t')[1]) for l in counts.strip().splitlines()];assert len(values)==2 and values[0]==values[1]
  (backup/'restore-proof.txt').write_text(counts)
 finally:run(['mysql','-e','DROP DATABASE '+restore])
 # Encrypt full recoverable code, media, environments, Apache config and DB.
 with (backup/'snapshot.tar.gz').open('wb') as f:
  run(['tar','--exclude=var/cache','--exclude=var/log','--exclude=.git','--exclude=node_modules','-czhf','-','-C','/',str(old).lstrip('/'),'var/www/oling.fr/public/uploads','etc/apache2','etc/oling',str(backup/'database.sql').lstrip('/')],stdout=f,stderr=subprocess.DEVNULL)
 run(['openssl','enc','-aes-256-cbc','-salt','-pbkdf2','-iter','200000','-in',str(backup/'snapshot.tar.gz'),'-out',str(backup/'snapshot.enc'),'-pass','file:'+str(key)])
 (backup/'snapshot.tar.gz').unlink();(backup/'database.sql').unlink()
 (backup/'snapshot.sha256').write_text(digest(backup/'snapshot.enc'))
 # Keep production dependencies exactly as already deployed. No copy from local vendor.
 release.mkdir();run(['rsync','-a','--exclude=var/cache/','--exclude=var/log/','--exclude=.git/','--exclude=node_modules/',str(old)+'/',str(release)+'/'])
 with tarfile.open(package) as t:
  for m in t.getmembers():
   assert m.isfile() and not m.name.startswith('/') and '..' not in Path(m.name).parts
   assert m.name.startswith(('src/Service/Crm/','templates/admin/crm/','scripts/crm/','docs/crm/')) or m.name in ['src/Controller/CrmAdminController.php','src/Controller/CrmImportController.php','src/Command/CrmSyncCommand.php','src/Command/CrmCheckCommand.php','migrations/Version20261022220000.php']
   if (release/m.name).exists():
    assert prior and prior['files'].get(m.name)==digest(old/m.name),m.name
  t.extractall(release,filter='data')
 run(['python3',str(release/'scripts/crm/patch-navigation.py'),str(release/'templates/admin/_nav.html.twig')])
 for p in [release/'var/cache',release/'var/log']:p.mkdir(parents=True,exist_ok=True)
 run(['chown','-R','deploy:www-data',str(release/'src/Service/Crm'),str(release/'templates/admin/crm'),str(release/'scripts/crm')])
 run(['chown','-R','www-data:www-data',str(release/'var/cache'),str(release/'var/log')])
 run(['chmod','-R','g+rwX',str(release/'var/cache'),str(release/'var/log')])
 # Migration isolated and additive; never execute unrelated pending migrations.
 if not prior: console('doctrine:migrations:execute','DoctrineMigrations\\Version20261022220000','--up','--no-interaction')
 console('cache:clear','--no-debug');console('lint:container','--no-debug');console('lint:twig','templates/admin/crm','--no-debug')
 run(['sudo','-u','www-data','env','APP_ENV=prod','APP_DEBUG=0','php','scripts/crm/recipe.php'],cwd=release)
 manifest={'commit':commit,'previous':str(old),'release':str(release),'package_sha256':digest(Path(package)),'backup_sha256':digest(backup/'snapshot.enc'),'files':{}}
 with tarfile.open(package) as t:
  for m in t.getmembers():manifest['files'][m.name]=digest(release/m.name)
 manifest['files']['templates/admin/_nav.html.twig']=digest(release/'templates/admin/_nav.html.twig')
 (backup/'manifest.json').write_text(json.dumps(manifest,indent=2));(release/'CRM_RELEASE.json').write_text(json.dumps(manifest,indent=2))
 print(json.dumps({'prepared':str(release),'backup':str(backup),'commit':commit}),flush=True)
elif phase=='activate':
 manifest=json.loads((backup/'manifest.json').read_text());assert str(old)==manifest['previous'];assert (backup/'offsite-verified').exists()
 for f,h in manifest['files'].items():assert digest(release/f)==h,f
 assert digest(Path(package))==manifest['package_sha256']
 run(['apache2ctl','configtest']);switch(release)
 try:
  for path,expected in [('/',200),('/contact',200),('/login',200),('/admin/crm',302)]:
   status=subprocess.check_output(['curl','--noproxy','*','--resolve','oling.fr:443:127.0.0.1','-sS','-o','/dev/null','-w','%{http_code}','https://oling.fr'+path],text=True);assert int(status)==expected,(path,status)
  run(['sudo','-u','www-data','env','APP_ENV=prod','APP_DEBUG=0','php','scripts/crm/recipe.php'],cwd=release)
  run(['systemctl','is-active','apache2','mysql'])
 except BaseException:
  switch(old);raise
 (backup/'activated').write_text(commit);print(json.dumps({'deployed':str(release),'commit':commit,'smoke':'PASS'}),flush=True)
else:raise SystemExit('Use prepare or activate')
