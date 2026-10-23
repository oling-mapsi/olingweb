<?php
namespace App\Service\Crm;

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/** Runs only after the existing secured controller has validated and saved the receipt. */
final class ContactReceiptSubscriber implements EventSubscriberInterface
{
    public function __construct(private FormQueue $queue,private Connection $db,private CrmConfig $config,private LoggerInterface $logger) {}
    public static function getSubscribedEvents(): array { return ['kernel.response'=>['onResponse',0]]; }
    public function onResponse(ResponseEvent $event): void
    {
        $r=$event->getRequest(); $response=$event->getResponse();
        if (!$this->config->enabled('forms') || !$event->isMainRequest() || !$r->isMethod('POST') || !$response instanceof JsonResponse) { return; }
        $route=$r->attributes->get('_route');
        if (!in_array($route,['send_mail','api_chat_conversation_lead'],true) || !$response->isSuccessful()) { return; }
        $receipt=json_decode($response->getContent(),true);
        if (empty($receipt['success'])) { return; }
        try {
            if ($route==='send_mail') {
                if (empty($receipt['persisted']) || empty($receipt['submission_id'])) { return; }
                $m=$this->db->fetchAssociative('SELECT * FROM messagerie WHERE submission_id=?',[$receipt['submission_id']]); if (!$m) { return; }
                $attribution=[]; foreach($r->request->all() as $key=>$value) { if (str_starts_with($key,'demand') && is_scalar($value)) { $attribution[$key]=mb_substr(strip_tags((string)$value),0,1000); } }
                $payload=FormQueue::olingPayload($m,$attribution);
            } else {
                // Read validated, persisted data only; never trust a request/response contact payload.
                $lead=$this->db->fetchAssociative("SELECT l.*,c.source_path,c.source_url FROM chat_lead l JOIN chat_conversation c ON c.id=l.conversation_id WHERE c.public_token=? AND l.rgpd_consent=1 AND c.submitted_at IS NOT NULL",[$r->attributes->get('token')]);
                if (!$lead) { return; }
                $payload=FormQueue::chatPayload($lead);
            }
            $result=$this->queue->enqueue($payload);
            $receipt['crm_status']=!empty($result['pending'])?'pending':(!empty($result['deleted'])?'ignored':'delivered');
        } catch (\Throwable) { $receipt['crm_status']='pending'; $this->logger->error('crm_form_delivery_pending',['channel'=>$route]); }
        $response->setData($receipt);
    }
}
