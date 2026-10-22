<?php
namespace App\Service\Crm;

/** Replaceable deterministic extraction boundary. Message text is never executed. */
class EmailExtractor
{
    public function extract(array $m, string $mailbox): ?array
    {
        if ($m['isDraft']??false) { return null; }
        $subject=trim($m['subject']??'');
        $body=mb_substr(html_entity_decode(strip_tags(preg_replace('/<\/?(?:p|div|br)[^>]*>/i',"\n",$m['body']['content']??$m['bodyPreview']??'')),ENT_QUOTES|ENT_HTML5,'UTF-8'),0,16000);
        $text=mb_strtolower($subject."\n".$body);
        $headers=[]; foreach ($m['internetMessageHeaders']??[] as $h) { $headers[strtolower($h['name'])]=$h['value']; }
        if (isset($headers['list-unsubscribe']) || isset($headers['list-id']) || preg_match('/newsletter|désabonn|unsubscribe|webinaire|webinar|code promo|test interne|\[test\]/u',$text)) { return null; }
        $from=$m['from']['emailAddress']??[];
        $sent=strtolower($from['address']??'')===strtolower($mailbox);
        // The existing website sends a structured self-addressed notification.
        if ($sent && $subject==='Nouveau message — Formulaire contact OLING' && preg_match('/^Email:\s*([^\r\n]+)$/mi',$body,$formEmail) && preg_match('/^Message:\s*\n(.*?)(?:\nAttribution de la demande:|$)/ms',$body,$formBody)) {
            $email=trim($formEmail[1]);
            if (!filter_var($email,FILTER_VALIDATE_EMAIL)) { return null; }
            $fields=['title'=>'Demande via formulaire OLING','summary'=>mb_substr(trim($formBody[1]),0,2000),'email'=>$email];
            foreach (['Société'=>'organization','Prénom'=>'contact'] as $label=>$field) { if (preg_match('/^'.$label.':\s*([^\r\n]+)$/mi',$body,$v)) { $fields[$field]=trim($v[1]); } }
            if (preg_match('/^Nom:\s*([^\r\n]+)$/mi',$body,$v)) { $fields['contact']=trim(($fields['contact']??'').' '.trim($v[1])); }
            return ['origin'=>'form','external_id'=>$mailbox.'|'.($m['internetMessageId']??$m['id']),'identity'=>$mailbox.'|'.($m['conversationId']??$m['id']),'title'=>$fields['title'],'kind'=>'private','fields'=>$fields,'url'=>$m['webLink']??'','excerpt'=>$body,'commercial'=>true];
        }
        $automatedDecision=(bool)preg_match('/confirmation.{0,25}dépôt|accusé.{0,25}dépôt|offre.{0,35}(retenue|rejetée)|marché.{0,25}(attribué|notification)/u',$text);
        $contact=$sent ? ($m['toRecipients'][0]['emailAddress']??[]) : $from;
        $email=strtolower($contact['address']??'');
        $ownDomain=substr(strrchr($mailbox,'@')?:'',1);
        if (!filter_var($email,FILTER_VALIDATE_EMAIL) || substr(strrchr($email,'@')?:'',1)===$ownDomain || (!$automatedDecision && preg_match('/no-?reply|nepasrepondre|mailer-daemon/i',$email))) { return null; }
        $commercial=$automatedDecision || (bool)preg_match('/demande.{0,50}(devis|proposition|accompagnement|intervention|mission)|proposition (commerciale|financière|technique)|appel d.offres|consultation|cahier des charges|besoin.{0,70}(accompagnement|conseil|formation|assistance)|pouvez.vous.{0,60}(proposer|accompagner|devis)|souhait.{0,60}(rencontrer|échanger|accompagn|mission)|devis|bon de commande|accusé.{0,20}(dépôt|reception)|offre.{0,30}(retenue|rejetée)|marché.{0,25}(attribué|notification)/us',$text);
        $status=null;
        if (preg_match('/offre.{0,35}(non retenue|rejetée)|proposition.{0,35}non retenue/u',$text)) { $status='lost'; }
        elseif (preg_match('/offre.{0,35}(est retenue|a été retenue)|marché.{0,25}(vous est attribué)|bon de commande signé/u',$text)) { $status='won'; }
        elseif (preg_match('/confirmation.{0,25}dépôt|accusé.{0,25}dépôt/u',$text) || ($sent && preg_match('/(ci.joint|en pièce jointe).{0,90}(proposition|devis|offre)/us',$text))) { $status='sent'; }
        $fields=['title'=>$subject?:'(Sans objet)','summary'=>mb_substr($body,0,2000),'contact'=>$contact['name']??'','email'=>$email];
        // Only explicit labels count as organization/amount; do not invent a company from an address.
        if (preg_match('/(?:Société|Organisation|Entreprise)\s*:\s*([^\r\n]{2,120})/ui',$body,$a)) { $fields['organization']=trim($a[1]); }
        if (preg_match('/(?:montant|budget)\s*(estimé|proposé)?\s*:\s*([0-9][0-9 .,]*)\s*(?:€|EUR)/ui',$body,$a)) { $amount=str_replace(' ','',trim($a[2])); if(str_contains($amount,',')) { $amount=str_replace(',','.',str_replace('.','',$amount)); } if(preg_match('/^\d{1,12}(\.\d{1,2})?$/D',$amount)) { $fields['amount']=$amount; $fields['amount_kind']=match(mb_strtolower($a[1]??'')) {'proposé'=>'proposed','estimé'=>'estimated',default=>''}; } }
        return ['origin'=>'email','external_id'=>$mailbox.'|'.($m['internetMessageId']??$m['id']),'identity'=>$mailbox.'|'.($m['conversationId']??$m['id']),'title'=>$fields['title'],'kind'=>preg_match('/marché public|appel d.offres|avis de marché/u',$text)?'public':'private','fields'=>$fields,'url'=>$m['webLink']??'','excerpt'=>$body,'suggested_status'=>$status,'commercial'=>$commercial];
    }
}
