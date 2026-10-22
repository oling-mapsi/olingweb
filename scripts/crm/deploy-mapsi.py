#!/usr/bin/env python3
"""Server-only narrow MAPSI patch. Run after CRM activation, while CRM timer is stopped."""
import hashlib,json,os,shutil,subprocess,sys,tarfile
from pathlib import Path
package,commit,oling_commit=sys.argv[1:]
assert all(len(x)==40 and all(c in '0123456789abcdef' for c in x) for x in [commit,oling_commit])
root=Path('/var/www/mapsi.fr'); backup=Path('/var/backups/oling')/('oling-crm-'+oling_commit[:12])/'mapsi-files'; backup.mkdir(mode=0o700)
old={'src/Controller/FormController.php':'4d8babaa897dd26006a4219339bd19e6f6e4e9847a9346e652cfa3fa472a18a9','templates/emails/form_request.html.twig':'66e4525ea6e3f65cdf088eac02b79e4a7e33fb3683c32dbdbe63f37b8cbdf276','templates/emails/form_request.txt.twig':'1378d7d0359932e33f4253b386e982681828d09f99b32446eac021b8a4703134'}
allowed=set(old)|{'src/Service/CrmFormDelivery.php','src/Command/CrmRetryCommand.php'}
with tarfile.open(package) as t:
 members=t.getmembers();assert {m.name for m in members}==allowed
 for m in members:
  assert m.isfile();dest=root/m.name
  if m.name in old: assert hashlib.sha256(dest.read_bytes()).hexdigest()==old[m.name],m.name
  else: assert not dest.exists(),m.name
 for m in members:
  if (root/m.name).exists():
   saved=backup/m.name;saved.parent.mkdir(parents=True,exist_ok=True);shutil.copy2(root/m.name,saved)
 try:
  for m in members:
   dest=root/m.name;temp=dest.with_suffix(dest.suffix+'.crm-new');temp.write_bytes(t.extractfile(m).read());shutil.chown(temp,user='deploy',group='www-data');temp.chmod(0o644);os.replace(temp,dest)
  subprocess.run(['sudo','-u','www-data','php','bin/console','cache:clear','--env=prod','--no-debug'],cwd=root,check=True)
  subprocess.run(['sudo','-u','www-data','php','bin/console','lint:container','--env=prod','--no-debug'],cwd=root,check=True)
  subprocess.run(['sudo','-u','www-data','php','bin/console','lint:twig','templates/emails','--env=prod','--no-debug'],cwd=root,check=True)
 except BaseException:
  for m in members:
   saved=backup/m.name
   if saved.exists():shutil.copy2(saved,root/m.name)
   else:(root/m.name).unlink(missing_ok=True)
  subprocess.run(['sudo','-u','www-data','php','bin/console','cache:clear','--env=prod','--no-debug'],cwd=root)
  raise
manifest={'commit':commit,'oling_commit':oling_commit,'files':{f:hashlib.sha256((root/f).read_bytes()).hexdigest() for f in allowed},'backup':str(backup)}
(root/'CRM_FORM_RELEASE.json').write_text(json.dumps(manifest,indent=2));print(json.dumps(manifest))
