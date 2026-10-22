#!/usr/bin/env python3
"""Run on production as www-data. Device OAuth tokens never leave the server."""
import json, os, time, urllib.request, urllib.parse, urllib.error
from pathlib import Path
config=json.loads(Path('/etc/oling/crm.json').read_text())
base='https://login.microsoftonline.com/'+config['tenant_id']+'/oauth2/v2.0/'
def post(endpoint,data):
 req=urllib.request.Request(base+endpoint,data=urllib.parse.urlencode(data).encode())
 try:
  with urllib.request.urlopen(req,timeout=30) as r: return json.load(r)
 except urllib.error.HTTPError as e: return json.load(e)
code=post('devicecode',{'client_id':config['client_id'],'scope':'https://graph.microsoft.com/Mail.Read offline_access'})
if 'device_code' not in code: raise SystemExit('Microsoft: '+code.get('error','erreur'))
print(json.dumps({k:code[k] for k in ['user_code','verification_uri','expires_in']}),flush=True)
interval=code.get('interval',5)
for _ in range(code['expires_in']//interval):
 time.sleep(interval)
 token=post('token',{'client_id':config['client_id'],'grant_type':'urn:ietf:params:oauth:grant-type:device_code','device_code':code['device_code']})
 if token.get('error')=='authorization_pending': continue
 if token.get('error')=='slow_down': time.sleep(5);continue
 if 'refresh_token' not in token: raise SystemExit('Microsoft: '+token.get('error','refresh_token absent'))
 path=Path(config.get('oauth_file','/var/lib/oling-crm/oauth.json'))
 fd=os.open(str(path)+'.tmp',os.O_WRONLY|os.O_CREAT|os.O_TRUNC,0o600)
 with os.fdopen(fd,'w') as f: json.dump({'refresh_token':token['refresh_token']},f)
 os.replace(str(path)+'.tmp',path)
 print('Microsoft 365 connecté. Jeton enregistré uniquement sur le serveur.',flush=True)
 break
else: raise SystemExit('Code expiré. Relancer la connexion.')
