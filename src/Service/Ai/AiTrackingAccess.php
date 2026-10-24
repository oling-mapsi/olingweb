<?php
namespace App\Service\Ai;
use Symfony\Component\HttpFoundation\Request;
/** Adapter seam for a separately approved OAuth resource-server integration.
 * No token fallback, session fallback, CRM token reuse or self-issued OAuth implementation.
 * Future implementation must validate issuer, audience/resource, expiry and granted scopes,
 * then map the subject to app_user and a stable execution actor. Never trust request payload claims.
 */
class AiTrackingAccess
{
    public function available(): bool {return false;}
    /** @return array{owner:int,actor:string,scopes:array<string>}|null */
    public function resolve(Request $request): ?array {return null;}
}
