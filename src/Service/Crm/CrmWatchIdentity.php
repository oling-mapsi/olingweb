<?php
namespace App\Service\Crm;

/** Exact identifiers only: never match unrelated tenders by their title. */
final class CrmWatchIdentity
{
    public static function aliases(array $fields): array
    {
        $normalize=static fn($v)=>mb_strtolower(preg_replace('/\s+/u',' ',trim((string)$v)));
        $lot=$normalize($fields['lot']??'');$aliases=[];
        if (!empty($fields['notice_id'])) { $aliases[]='notice|'.$fields['notice_id'].'|lot|'.($fields['lot']??''); }
        if (!empty($fields['buyer']) && !empty($fields['reference'])) {
            $aliases[]=$fields['buyer'].'|'.$fields['reference'].'|'.($fields['lot']??'');
            $aliases[]='reference-v2|'.CrmStore::json([$normalize($fields['buyer']),$normalize($fields['reference']),$lot]);
        }
        return array_unique($aliases);
    }
}
