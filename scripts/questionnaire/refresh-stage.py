from pathlib import Path
import os,json,hashlib,subprocess,tarfile,fcntl
work=Path('/home/ubuntu/oling-questionnaire-20261025');m=json.loads((work/'manifest.json').read_text());old=Path(m['expected_release']);new=old.with_name('oling-questionnaire-context-20261025')
lock=open('/var/lock/oling-deploy-cms-p0.lock','w');fcntl.flock(lock,fcntl.LOCK_EX|fcntl.LOCK_NB)
assert Path('/var/www/oling-current').resolve()==old
sha=lambda p:hashlib.sha256(p.read_bytes()).hexdigest()
for f in m['files']:assert (sha(old/f['path']) if (old/f['path']).exists() else None)==f['before']
with tarfile.open(work/'payload.tgz') as a:
 for e in a.getmembers():
  assert e.isfile() and not Path(e.name).is_absolute() and '..' not in Path(e.name).parts
  assert e.name in {f['path'] for f in m['files']} or e.name.startswith('public/build/')
  p=new/e.name;p.parent.mkdir(parents=True,exist_ok=True)
  if p.exists():p.unlink()
  p.write_bytes(a.extractfile(e).read());p.chmod(0o644)
for f in m['files']:assert sha(new/f['path'])==f['after']
subprocess.run(['sudo','-u','www-data','env','APP_ENV=prod','APP_DEBUG=0','/usr/bin/php8.4','bin/console','cache:clear','--no-ansi'],cwd=new,check=True)
subprocess.run(['sudo','-u','www-data','env','APP_ENV=prod','APP_DEBUG=0','/usr/bin/php8.4',str(work/'render-release.php'),str(new),str(work/'rendered')],cwd=new,check=True)
assert Path('/var/www/oling-current').resolve()==old
print('STAGE_REFRESH_OK; production pointer unchanged')
