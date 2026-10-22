<?php
namespace App\Service\Crm;

/** Replaceable deterministic extraction boundary. Message text is never executed. */
class EmailExtractor
{
    public function extract(array $m, string $mailbox): ?array
    {
        if ($m['isDraft']??false) { return null; }
        $subject=trim($m['subject']??'');
        $body=mb_substr(html_entity_decode(strip_tags(preg_replace('/<\/?(?:p|div|br)[^>]*>/i',"\n",$m['body']['content']??$m['bodyPreview']??'')),ENT_QUOTES|ENT_HTML5,'UTF-8'),0,200000);
        $text=mb_strtolower($subject."\n".$body);
        $headers=[]; foreach ($m['internetMessageHeaders']??[] as $h) { $headers[strtolower($h['name'])]=$h['value']; }
        if (isset($headers['list-unsubscribe']) || isset($headers['list-id']) || preg_match('/newsletter|désabonn|unsubscribe|webinaire|webinar|code promo|test interne|\[test\]|veille.{0,30}marchés|sélection.{0,35}appels d.offres|alerte.{0,30}marchés|marchés.{0,25}(du jour|cette semaine)|marchesonline|doubletrade|vecteurplus|achatpublic.info/u',$text)) { return null; }
        $from=$m['from']['emailAddress']??[];
        $sent=strtolower($from['address']??'')===strtolower($mailbox);
        // Exact sender + subject + shared reference; the technical sender is never a prospect.
        $trusted=$sent || strtolower($from['address']??'')==='florestan.rouet@oling.fr';
        if ($trusted && $subject==='Nouveau message — Formulaire contact OLING' && preg_match('/^Référence de demande\s*:\s*([a-zA-Z0-9_-]{8,100})$/mi',$body,$ref)) {
            $f=[]; foreach(['Prénom'=>'first_name','Nom'=>'last_name','Société'=>'organization','Email'=>'email','Téléphone'=>'phone'] as $label=>$key) {
                if (preg_match('/^'.$label.':\s*([^\r\n]+)$/mi',$body,$v)) { $f[$key]=trim($v[1]); }
            }
            if (!preg_match('/^Message:\s*\n(.*?)(?:\n\n(?:Attribution de la demande:|Source:)|$)/ms',$body,$v)) { return null; }
            $f['summary']=trim($v[1]); $f['form_name']='contact'; $f['received_at']=$m['receivedDateTime']??'';
            try { return FormIntake::source(['site'=>'OLING','reference'=>$ref[1],'fields'=>$f],$mailbox.'|'.($m['internetMessageId']??$m['id']),$m['webLink']??''); } catch (\InvalidArgumentException) { return null; }
        }
        if ($trusted && preg_match("/^(Demande (de contact|de démo|d’essai gratuit|d'essai gratuit)|Contact request|Demo request|Free trial request) - Mapsi$/u",$subject)
            && preg_match('/CRM-MAPSI: ([A-Za-z0-9+\/=]+) END-CRM/u',$body,$encoded)) {
            $payload=json_decode(base64_decode($encoded[1],true)?:'',true);
            try { return FormIntake::source($payload??[],$mailbox.'|'.($m['internetMessageId']??$m['id']),$m['webLink']??''); } catch (\InvalidArgumentException) { return null; }
        }
        $automatedDecision=(bool)preg_match('/confirmation.{0,25}dépôt|accusé.{0,25}dépôt|offre.{0,35}(retenue|rejetée)|marché.{0,25}(attribué|notification)/u',$text);
        $contact=$sent ? ($m['toRecipients'][0]['emailAddress']??[]) : $from;
        $email=strtolower($contact['address']??'');
        $ownDomain=substr(strrchr($mailbox,'@')?:'',1);
        if (!filter_var($email,FILTER_VALIDATE_EMAIL) || substr(strrchr($email,'@')?:'',1)===$ownDomain || (!$automatedDecision && preg_match('/no-?reply|nepasrepondre|mailer-daemon/i',$email))) { return null; }
        // A directed request needs an explicit first-person intention AND an addressed ask.
        // Generic procurement words and automated deposit notifications cannot create leads.
        $commercial=!$sent && !$automatedDecision
            && (bool)preg_match('/(?:nous|je|notre entreprise).{0,100}(?:souhait|recherch|avons besoin|ai besoin|a besoin)/us',$text)
            && (bool)preg_match('/(?:pouvez.vous|pourriez.vous|merci de (?:nous|me)|vous contacter|votre accompagnement|avec vous)/us',$text);
        $status=null;
        if (preg_match('/offre.{0,35}(non retenue|rejetée)|proposition.{0,35}non retenue/u',$text)) { $status='lost'; }
        elseif (preg_match('/offre.{0,35}(est retenue|a été retenue)|marché.{0,25}(vous est attribué)|bon de commande signé/u',$text)) { $status='won'; }
        elseif (preg_match('/confirmation.{0,25}dépôt|accusé.{0,25}dépôt/u',$text) || ($sent && preg_match('/(ci.joint|en pièce jointe).{0,90}(proposition|devis|offre)/us',$text))) { $status='sent'; }
        // Sent offers may open a qualification candidate only with explicit attachment wording.
        $commercial=$commercial || ($sent && $status==='sent');
        $fields=['title'=>$subject?:'(Sans objet)','summary'=>mb_substr($body,0,2000),'contact'=>$contact['name']??'','email'=>$email];
        // Only explicit labels count as organization/amount; do not invent a company from an address.
        if (preg_match('/(?:Société|Organisation|Entreprise)\s*:\s*([^\r\n]{2,120})/ui',$body,$a)) { $fields['organization']=trim($a[1]); }
        if (preg_match('/(?:montant|budget)\s*(estimé|proposé)?\s*:\s*([0-9][0-9 .,]*)\s*(?:€|EUR)/ui',$body,$a)) { $amount=str_replace(' ','',trim($a[2])); if(str_contains($amount,',')) { $amount=str_replace(',','.',str_replace('.','',$amount)); } if(preg_match('/^\d{1,12}(\.\d{1,2})?$/D',$amount)) { $fields['amount']=$amount; $fields['amount_kind']=match(mb_strtolower($a[1]??'')) {'proposé'=>'proposed','estimé'=>'estimated',default=>''}; } }
        return ['origin'=>'email','external_id'=>$mailbox.'|'.($m['internetMessageId']??$m['id']),'identity'=>$mailbox.'|'.($m['conversationId']??$m['id']),'title'=>$fields['title'],'kind'=>'private','fields'=>$fields,'url'=>$m['webLink']??'','excerpt'=>$body,'suggested_status'=>$status,'commercial'=>$commercial];
    }
}
