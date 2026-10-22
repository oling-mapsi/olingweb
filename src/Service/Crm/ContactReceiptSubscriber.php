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
        if (!$this->config->enabled('forms') || !$event->isMainRequest() || !$r->isMethod('POST') || !$response instanceof JsonResponse || $r->attributes->get('_route')!=='send_mail') { return; }
        $receipt=json_decode($response->getContent(),true);
        if (empty($receipt['success']) || empty($receipt['persisted']) || empty($receipt['submission_id'])) { return; }
        try {
            $m=$this->db->fetchAssociative('SELECT * FROM messagerie WHERE submission_id=?',[$receipt['submission_id']]); if (!$m) { return; }
            $attribution=[]; foreach($r->request->all() as $k=>$v) { if (str_starts_with($k,'demand') && is_scalar($v)) { $attribution[$k]=mb_substr(strip_tags((string)$v),0,1000); } }
            $result=$this->queue->enqueue(FormQueue::olingPayload($m,$attribution));
            $receipt['crm_status']=!empty($result['pending'])?'pending':'delivered';
        } catch (\Throwable) { $receipt['crm_status']='pending'; $this->logger->error('crm_form_delivery_pending',['reference'=>$receipt['submission_id']]); }
        $response->setData($receipt);
    }
}
