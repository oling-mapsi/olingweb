from pathlib import Path
import os,json,hashlib,subprocess,tarfile,shutil,fcntl
os.umask(0o022)
work=Path('/home/ubuntu/oling-questionnaire-20261025');manifest=json.loads((work/'manifest.json').read_text())
pointer=Path('/var/www/oling-current');old=pointer.resolve();new=old.with_name('oling-questionnaire-context-20261025')
lock=open('/var/lock/oling-deploy-cms-p0.lock','w');fcntl.flock(lock,fcntl.LOCK_EX|fcntl.LOCK_NB)
assert str(old)==manifest['expected_release'],'Active release changed'
sha=lambda p:hashlib.sha256(p.read_bytes()).hexdigest()
for f in manifest['files']:
 p=old/f['path']
 assert (sha(p) if p.exists() else None)==f['before'],('Source drift',f['path'])
assert not new.exists(),'Stage already exists'
subprocess.run(['cp','-al',str(old),str(new)],check=True)
for folder in ['cache','log']:
 p=new/'var'/folder;assert not p.is_symlink();shutil.rmtree(p);p.mkdir(mode=0o775);os.chown(p,33,33)
with tarfile.open(work/'payload.tgz') as archive:
 for entry in archive.getmembers():
  assert entry.isfile() and not Path(entry.name).is_absolute() and '..' not in Path(entry.name).parts
  assert entry.name in {f['path'] for f in manifest['files']} or entry.name.startswith('public/build/')
  p=new/entry.name;p.parent.mkdir(parents=True,exist_ok=True)
  if p.exists():p.unlink() # Never mutate a hardlink shared with the previous release.
  p.write_bytes(archive.extractfile(entry).read());p.chmod(0o644)
for f in manifest['files']:assert sha(new/f['path'])==f['after']
for f in manifest['files']:
 if f['path'].endswith('.php'):subprocess.run(['/usr/bin/php8.4','-l',str(new/f['path'])],check=True)
for args in [['cache:warmup'],['lint:twig','templates/includes/_chat_widget_v2.html.twig','templates/seo/_db-landing.html.twig','templates/public/_page_hero.html.twig','templates/public/_cta_band.html.twig']]:
 subprocess.run(['sudo','-u','www-data','env','APP_ENV=prod','APP_DEBUG=0','/usr/bin/php8.4','bin/console',*args,'--no-ansi'],cwd=new,check=True)
out=work/'rendered';out.mkdir(mode=0o775);os.chown(out,33,33)
subprocess.run(['sudo','-u','www-data','env','APP_ENV=prod','APP_DEBUG=0','/usr/bin/php8.4',str(work/'render-release.php'),str(new),str(out)],cwd=new,check=True)
assert pointer.resolve()==old
(work/'stage-proof.json').write_text(json.dumps({'old':str(old),'new':str(new),'production_unchanged':True,'schema_changed':False},indent=2))
print('STAGE_OK; production pointer unchanged')
