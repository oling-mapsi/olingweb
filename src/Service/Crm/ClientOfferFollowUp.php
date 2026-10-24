<?php
namespace App\Service\Crm;

final class ClientOfferFollowUp
{
    /** A new sent transition replaces the previous action; later edits remain manual. */
    public static function onTransition(array $deal, string $status, string $kind, \DateTimeImmutable $now): array
    {
        if ($status !== 'sent' || $deal['status'] === 'sent' || $kind !== 'private' || $deal['origin'] === 'watch') {
            return [];
        }
        // Calendar days in Paris, preserving local time across daylight-saving changes.
        $reminder = $now->setTimezone(new \DateTimeZone('Europe/Paris'))->modify('+7 days');
        return [
            'next_action' => 'Relancer le client sur l’offre envoyée',
            'reminder' => $reminder->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z'),
        ];
    }
}
