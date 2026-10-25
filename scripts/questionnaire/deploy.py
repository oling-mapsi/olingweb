from pathlib import Path
import os,json,hashlib,subprocess,fcntl,urllib.request,re,concurrent.futures,datetime,tarfile,time
work=Path('/home/ubuntu/oling-questionnaire-20261025');m=json.loads((work/'manifest.json').read_text());pointer=Path('/var/www/oling-current');old=Path(m['expected_release']);new=old.with_name('oling-questionnaire-context-20261025')
lock=open('/var/lock/oling-deploy-cms-p0.lock','w');fcntl.flock(lock,fcntl.LOCK_EX|fcntl.LOCK_NB)
sha=lambda p:hashlib.sha256(p.read_bytes()).hexdigest()
assert pointer.resolve()==old,'Active release changed'
assert sha(work/'payload.tgz')==m['archive_sha256']
for f in m['files']:
 assert (sha(old/f['path']) if (old/f['path']).exists() else None)==f['before'],('Old file drift',f['path'])
 assert sha(new/f['path'])==f['after'],('Staged file drift',f['path'])
with tarfile.open(work/'payload.tgz') as a:
 for f in a.getmembers():assert hashlib.sha256(a.extractfile(f).read()).hexdigest()==sha(new/f.name),f.name
rows=json.loads((work/'rendered/render-proof.json').read_text());paths=[r['path'] for r in rows]
def get(p):
 req=urllib.request.Request('https://oling.fr'+p,headers={'User-Agent':'OLING-questionnaire-readonly-verification'})
 with urllib.request.urlopen(req,timeout=30) as r:return r.status,r.read().decode()
def seo(html):
 patterns={'title':r'<title[^>]*>(.*?)</title>','h1':r'<h1\b[^>]*>(.*?)</h1>','canonical':r'<link\b[^>]*rel="canonical"[^>]*>','robots':r'<meta\b[^>]*name="robots"[^>]*>','hreflang':r'<link\b[^>]*hreflang[^>]*>','schema':r'<script[^>]*type="application/ld\+json"[^>]*>(.*?)</script>'}
 return {k:re.findall(p,html,re.S) for k,p in patterns.items()}
def before(p):
 status,html=get(p);assert status==200,p
 return p,seo(html)
with concurrent.futures.ThreadPoolExecutor(max_workers=3) as pool:baseline=dict(pool.map(before,paths))
def switch(target):
 temp=pointer.with_name('oling-current.questionnaire-next');assert not os.path.lexists(temp);temp.symlink_to(target);os.replace(temp,pointer)
def reload_runtime():
 subprocess.run(['docker','kill','--signal=USR2','oling-php84'],check=True)
 subprocess.run(['systemctl','reload','apache2'],check=True)
subprocess.run(['apache2ctl','configtest'],check=True)
assert pointer.resolve()==old
switch(new)
try:
 reload_runtime()
 # FPM workers retain PHP realpath/opcache across an Apache-only reload.
 for attempt in range(15):
  try:
   status,html=get('/crm')
   if status==200 and 'data-qualification-context="crm"' in html and 'data-chat-erp-form' not in html:break
  except Exception:pass
  time.sleep(1)
 else:raise RuntimeError('New PHP release did not become ready')
 checks=[]
 for row in rows:
  p=row['path'];status,html=get(p);assert status==200,p
  assert seo(html)==baseline[p],('SEO drift',p)
  assert ('data-chat-erp-form' in html)==row['erp'],('ERP form policy',p)
  if not row['erp']:assert not re.search(r'Questionnaire ERP|Qualifier un besoin ERP|Lancer le questionnaire ERP|/erp-progiciel/questionnaire|erp-questionnaire',html),p
  assert 'qualification.prefill.' not in html,('Missing translation',p)
  if p=='/erp-progiciel':assert 'href="/erp-progiciel/questionnaire"' in html
  checks.append({'path':p,'http':status,'erp':row['erp'],'seo_unchanged':True})
 entry=json.loads((new/'public/build/entrypoints.json').read_text())['entrypoints']['app']
 for asset in entry.get('js',[])+entry.get('css',[]):
  with urllib.request.urlopen('https://oling.fr'+asset,timeout=30) as response:
   assert response.status==200
   assert hashlib.sha256(response.read()).hexdigest()==sha(new/'public'/asset.lstrip('/')),asset
 proof={'deployed':True,'at':datetime.datetime.now(datetime.timezone.utc).isoformat(),'release':str(new),'previous_release':str(old),'checks':checks,'assets_verified':True,'database_changed':False,'production_form_submissions':0,'rollback':'Atomic switch of /var/www/oling-current to previous_release, then reload apache2'}
 (work/'deployment-proof.json').write_text(json.dumps(proof,indent=2));print(json.dumps(proof,indent=2))
except BaseException:
 switch(old);reload_runtime();print('ROLLED_BACK');raise
