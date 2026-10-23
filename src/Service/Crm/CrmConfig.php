<?php
namespace App\Service\Crm;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class CrmConfig
{
    private array $settings = [];
    public function __construct(#[Autowire('%kernel.environment%')] private string $environment)
    {
        // Production secrets live outside releases. Never load them in dev/test.
        if ($environment === 'prod' && is_readable('/etc/oling/crm.json')) {
            $this->settings = json_decode(file_get_contents('/etc/oling/crm.json'), true, 32, JSON_THROW_ON_ERROR);
        }
    }
    public function get(string $key, mixed $default = ''): mixed { return $this->settings[$key] ?? $default; }
    public function enabled(string $channel): bool { return $this->environment === 'prod' && $this->get($channel.'_enabled', false) === true; }
    public function graphReady(): bool
    {
        if ($this->get('client_intake','forms_only') !== 'email_and_forms') { return false; }
        foreach (['tenant_id','client_id','mailbox'] as $key) { if (!$this->get($key)) { return false; } }
        return $this->enabled('email') && ($this->get('client_secret') || is_readable($this->get('oauth_file','/var/lib/oling-crm/oauth.json')));
    }
    public function status(): array
    {
        return ['environment'=>$this->environment, 'client_intake'=>$this->get('client_intake','forms_only'), 'forms'=>$this->enabled('forms'), 'email'=>$this->graphReady(), 'watch'=>$this->enabled('watch') && strlen($this->get('import_token')) >= 32, 'scheduler'=>$this->enabled('scheduler')];
    }
}
