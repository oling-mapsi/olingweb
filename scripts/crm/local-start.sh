#!/bin/sh
set -eu
cd "$(dirname "$0")/../.."
export APP_ENV=dev APP_DEBUG=1 MAILER_DSN=null://null GROWTH_FACTORY_ENABLED=0
export DATABASE_URL="sqlite:///$PWD/var/crm-local.db"
export OPENAI_API_KEY='' MISTRAL_API_KEY='' CHAT_AI_PROVIDER=disabled
php scripts/crm/local-init.php
exec php -S 127.0.0.1:8092 -t public scripts/crm/router.php
