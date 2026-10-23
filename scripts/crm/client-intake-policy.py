#!/usr/bin/env python3
"""Server-only. Disable email intake and freeze exactly the visible private client records."""
import datetime, fcntl, json, os, subprocess
from pathlib import Path
assert os.geteuid()==0
lock=open('/var/lock/oling-deploy-cms-p0.lock','a');fcntl.flock(lock,fcntl.LOCK_EX|fcntl.LOCK_NB)
folder=Path('/var/backups/oling/crm-client-reset-20261023');folder.mkdir(mode=0o700,exist_ok=True)
plan=Path('/var/lib/oling-crm/client-reset-20261023.json')
assert not plan.exists(), 'Plan already exists; do not extend deletion to newer requests.'
config=Path('/etc/oling/crm.json');original=config.read_bytes();settings=json.loads(original)
(folder/'configuration-before.json').write_bytes(original);(folder/'configuration-before.json').chmod(0o600)
subprocess.run(['systemctl','stop','oling-crm-sync.timer','oling-crm-sync.service'],check=True)
try:
    settings['email_enabled']=False;settings['client_intake']='forms_only'
    tmp=config.with_suffix('.json.next');tmp.write_text(json.dumps(settings,ensure_ascii=False,indent=2)+'\n')
    os.chown(tmp,config.stat().st_uid,config.stat().st_gid);tmp.chmod(0o640);os.replace(tmp,config)
    query="""SELECT id FROM crm_deal WHERE kind='private'
    AND COALESCE(JSON_UNQUOTE(JSON_EXTRACT(data,'$.origin')),'') <> 'watch'
    AND COALESCE(JSON_UNQUOTE(JSON_EXTRACT(data,'$.review_required')),'false')='false'
    AND COALESCE(JSON_UNQUOTE(JSON_EXTRACT(data,'$.merged_into')),'')='' ORDER BY id"""
    ids=subprocess.check_output(['mysql','-N','oling_db','-e',query],text=True).splitlines()
    assert all(len(i)==32 and all(c in '0123456789abcdef' for c in i) for i in ids)
    data={'at':datetime.datetime.now(datetime.timezone.utc).isoformat(),'ids':ids,'count':len(ids),'scope':'visible private client records only; excludes public markets, review queue and merged records'}
    plan.write_text(json.dumps(data));os.chown(plan,0,config.stat().st_gid);plan.chmod(0o640)
    print(json.dumps({'email_enabled':False,'client_intake':'forms_only','planned_clients':len(ids),'plan':str(plan)}))
finally:
    subprocess.run(['systemctl','start','oling-crm-sync.timer'],check=True)
