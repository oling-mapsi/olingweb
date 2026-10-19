<?php
namespace App\Service;

use App\Entity\Messagerie;
use Doctrine\DBAL\Connection;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Email;

final class ContactNotificationService
{
    public function __construct(private readonly Connection $db, private readonly MailerInterface $mailer) {}

    public function notify(Messagerie $message, array $demand = []): string
    {
        $id=$message->getSubmissionId();
        if (!$id || !$message->getId()) { throw new \LogicException('A persisted receipt is required'); }
        // Atomic claim prevents concurrent initial/retry notifications. Historical unknowns are excluded.
        $claimed=$this->db->executeStatement("UPDATE messagerie SET notification_status='sending' WHERE id=? AND submission_id=? AND notification_status IN ('pending','failed')",[$message->getId(),$id]);
        if ($claimed!==1) {
            $status=(string)$this->db->fetchOne('SELECT notification_status FROM messagerie WHERE id=?',[$message->getId()]);
            $message->setNotificationStatus($status);
            return $status;
        }
        $message->setNotificationStatus('sending');
        $email=(new Email())->from('florestan.rouet@oling.fr')->to('florestan.rouet@oling.fr')
            ->replyTo($message->getWorkEmail())->subject('Nouveau message — Formulaire contact OLING')
            ->text("Référence de demande : {$id}\nPrénom: {$message->getFirstName()}\nNom: {$message->getLastName()}\nSociété: {$message->getCompany()}\nEmail: {$message->getWorkEmail()}\n\nMessage:\n{$message->getDetails()}\n\n".
                implode("\n",array_map(static fn($k,$v)=>$k.': '.($v!==''?$v:'non disponible'),array_keys($demand),array_values($demand))));
        try { $this->mailer->send($email); $status='sent'; }
        catch (TransportExceptionInterface) { $status='failed'; }
        // A failure/crash after transport acceptance leaves 'sending': operator review, never blind replay.
        $this->db->executeStatement('UPDATE messagerie SET notification_status=? WHERE id=? AND submission_id=? AND notification_status=?',[$status,$message->getId(),$id,'sending']);
        $message->setNotificationStatus($status);
        return $status;
    }
}
