export function contactReceiptEvent(response, seen, excluded, consent) {
  if (excluded || !consent || response?.success !== true || response?.persisted !== true || !/^[a-f0-9]{32}$/.test(response?.submission_id || '')) return null;
  if (seen.has(response.submission_id)) return null;
  seen.add(response.submission_id);
  return { name: 'contact_confirmed', event_id: `contact_${response.submission_id}`, submission_id: response.submission_id, notification_status: response.notification_status };
}
