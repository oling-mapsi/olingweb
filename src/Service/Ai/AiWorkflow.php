<?php
namespace App\Service\Ai;
final class AiWorkflow
{
    public const STATES=['draft'=>'À cadrer','queued'=>'En attente du Mac','running'=>'En cours','reviewing'=>'Revue Codex','awaiting_review'=>'À valider','blocked'=>'Arbitrage requis','interrupted'=>'Interrompue','cancelled'=>'Annulée','closed'=>'Clôturée'];
    public const MAX_CORRECTIONS=2;
    public static function role(string $role): array
    {
        // Explicit persisted policy. Availability is checked by the relay, never silently substituted.
        return match($role) {
            'execute','correct'=>['model'=>'gpt-6.1-sol','effort'=>'medium'],
            'review'=>['model'=>'gpt-6.1-sol','effort'=>'high'],
            default=>throw new \InvalidArgumentException('Rôle inconnu.'),
        };
    }
    public static function assertState(string $state,array $allowed): void
    {if(!in_array($state,$allowed,true))throw new \DomainException('Transition indisponible depuis cet état.');}
}
