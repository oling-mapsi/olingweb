from pathlib import Path
import os,json,subprocess,fcntl
work=Path('/home/ubuntu/oling-questionnaire-20261025');m=json.loads((work/'manifest.json').read_text())
old=Path(m['expected_release']);new=old.with_name('oling-questionnaire-context-20261025')
lock=open('/var/lock/oling-deploy-cms-p0.lock','w');fcntl.flock(lock,fcntl.LOCK_EX|fcntl.LOCK_NB)
assert Path('/var/www/oling-current').resolve()==old
# Composer generates its maps in the staged release only; detach shared hardlinks first.
for p in [new/'vendor/autoload.php', *list((new/'vendor/composer').rglob('*'))]:
 if p.is_file() and not p.is_symlink():
  data=p.read_bytes();mode=p.stat().st_mode&0o777;p.unlink();p.write_bytes(data);p.chmod(mode)
subprocess.run(['/usr/bin/composer','dump-autoload','--no-dev','--classmap-authoritative','--no-scripts','--no-plugins'],cwd=new,env={**os.environ,'COMPOSER_ALLOW_SUPERUSER':'1'},check=True)
for args in [['cache:warmup'],['lint:twig','templates/includes/_chat_widget_v2.html.twig','templates/seo/_db-landing.html.twig','templates/public/_page_hero.html.twig','templates/public/_cta_band.html.twig']]:
 subprocess.run(['sudo','-u','www-data','env','APP_ENV=prod','APP_DEBUG=0','/usr/bin/php8.4','bin/console',*args,'--no-ansi'],cwd=new,check=True)
out=work/'rendered';out.mkdir(mode=0o775,exist_ok=True);os.chown(out,33,33)
subprocess.run(['sudo','-u','www-data','env','APP_ENV=prod','APP_DEBUG=0','/usr/bin/php8.4',str(work/'render-release.php'),str(new),str(out)],cwd=new,check=True)
assert Path('/var/www/oling-current').resolve()==old
(work/'stage-proof.json').write_text(json.dumps({'old':str(old),'new':str(new),'production_unchanged':True,'schema_changed':False},indent=2))
print('STAGE_OK; production pointer unchanged')
