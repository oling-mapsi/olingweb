const {chromium}=require('playwright');
const fs=require('node:fs');const path=require('node:path');const assert=require('node:assert/strict');
const dir='/tmp/oling-questionnaire-evidence';
const pages=[['/facturation-electronique-amoa','rfe','2027'],['/crm','crm','référentiel client'],['/consulting/amoa-ia-pilotage-de-projets-ia-et-agents-metier','amoa-ia','critères de passage'],['/expertises/amoa-ia-pilotage-projets-agents','amoa-ia','critères de passage'],['/expertises/rgpd-dpo-gouvernance','dpo','AIPD']];
const htmlFile=p=>dir+'/staged/'+p.slice(1).replaceAll('/','_')+'.html';
(async()=>{
 const browser=await chromium.launch({headless:true});const results=[];
 try{
 for(const width of [1366,390])for(const [pathname,need,keyword] of pages){
  const context=await browser.newContext({viewport:{width,height:width===390?844:900}});const page=await context.newPage();let mode='consent',created=null,posts=0;const errors=[];
  page.on('pageerror',e=>errors.push(e.message));
  await page.addInitScript(()=>{localStorage.clear();localStorage.setItem('oling_cookie_consent','all');window.__chatEvents=[];document.addEventListener('oling:chat-analytics',e=>window.__chatEvents.push(e.detail));});
  const conversation={token:'synthetic-browser-token',status:'active',messages:[],leadSubmitted:false,requestLead:true,qualification:{},contact:null};
  await page.route('**/*',async route=>{
   const req=route.request(),url=new URL(req.url());
   const json=(body,status=200)=>route.fulfill({status,contentType:'application/json',body:JSON.stringify(body)});
   if(url.pathname==='/api/chat/conversations'&&req.method()==='POST'){created=req.postDataJSON();return json(conversation,201);}
   if(url.pathname.endsWith('/lead')&&req.method()==='POST'){
    posts++;const body=req.postDataJSON();
    if(!body.rgpdConsent)return json({success:false,message:'Votre consentement est nécessaire.'},400);
    if(mode==='pending')return json({success:false,persisted:true,crm_status:'pending',message:'Votre demande est enregistrée. Transmission en attente.'},202);
    return json({success:true,crm_status:'delivered',conversation:{...conversation,leadSubmitted:true,requestLead:false}});
   }
   if(url.pathname==='/analytics/collect')return json({success:true});
   if(req.method()!=='GET')return route.abort();
   if(url.origin==='https://oling.fr'&&fs.existsSync(htmlFile(url.pathname)))return route.fulfill({contentType:'text/html',body:fs.readFileSync(htmlFile(url.pathname),'utf8')});
   if(url.origin==='https://oling.fr'&&url.pathname.startsWith('/build/')){
    const f='/tmp/oling-questionnaire-build/public'+url.pathname;
    if(fs.existsSync(f))return route.fulfill({path:f,contentType:f.endsWith('.css')?'text/css':f.endsWith('.js')?'application/javascript':undefined});
   }
   if(url.origin==='https://oling.fr'&&/^\/(images|img|css|js|styles|assets|build)\//.test(url.pathname))return route.continue();
   return route.fulfill({status:204,body:''});
  });
  await page.goto('https://oling.fr'+pathname,{waitUntil:'networkidle'});
  assert.equal(await page.locator('[data-chat-erp-form],[data-erp-questionnaire-url-template]').count(),0);
  assert.equal(await page.locator('#oling-chat-widget').getAttribute('data-qualification-context'),need);
  const cta=page.locator('main a.btn[href*="source_path="]').first();assert.ok(await cta.count());
  assert.equal(new URL(await cta.getAttribute('href'),'https://oling.fr').searchParams.get('source_path'),pathname);
  const launcher=page.locator('.oling-chat-widget__launcher');await launcher.focus();await launcher.press('Enter');
  await page.locator('#chatFullName').waitFor({state:'visible'});
  assert.equal(created.sourcePath,pathname);assert.equal(created.sourceUrl,'https://oling.fr'+pathname);
  const prefill=await page.locator('#chatNeedDescription').inputValue();assert.ok(prefill.includes(keyword),prefill);assert.ok(!/\bERP\b/.test(prefill));
  await page.locator('#chatFullName').fill('Recette fictive');await page.locator('#chatEmail').fill('fixture@example.invalid');await page.locator('#chatCompany').fill('Organisation fictive');
  const submit=page.locator('[data-chat-submit-lead]');await submit.click();await page.locator('[data-chat-error]').filter({hasText:'consentement'}).waitFor();
  mode='pending';await page.locator('[name="chatConsent"]').check();await submit.click();await page.locator('[data-chat-error]').filter({hasText:'attente'}).waitFor();
  assert.equal(await page.evaluate(()=>window.__chatEvents.filter(e=>e.name==='lead_confirmed').length),0);
  await page.screenshot({path:dir+`/browser-${need}-${width}-${pathname.includes('expertises')?'expertise':'owner'}.png`});
  mode='delivered';await submit.click();await page.locator('[data-chat-summary][data-tone="success"]').waitFor();
  assert.equal(await page.evaluate(()=>window.__chatEvents.filter(e=>e.name==='lead_confirmed').length),1);
  assert.equal(await page.evaluate(()=>window.__chatEvents.filter(e=>e.name==='erp_questionnaire_confirmed').length),0);
  assert.equal(await page.evaluate(()=>window.__chatEvents.find(e=>e.name==='lead_confirmed').parameters.demand_cluster),need);
  await page.keyboard.press('Escape');assert.equal(await launcher.getAttribute('aria-expanded'),'false');assert.ok(await launcher.evaluate(el=>el===document.activeElement));
  const overflow=await page.evaluate(()=>document.documentElement.scrollWidth>window.innerWidth+2);assert.equal(overflow,false);
  assert.deepEqual(errors,[]);results.push({path:pathname,width,context:need,consentRefused:true,pendingHonest:true,confirmedOnce:true,keyboard:true,overflow:false,api:'synthetic local responses; no production submission'});
  await context.close();console.log('PASS',width,pathname);
 }
 fs.writeFileSync(dir+'/browser-proof.json',JSON.stringify(results,null,2));
 }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exit(1)});
