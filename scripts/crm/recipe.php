<?php
// Compatibility wrapper; the command runs synthetic operations inside a rolled-back transaction.
passthru(escapeshellarg(PHP_BINARY).' '.escapeshellarg(dirname(__DIR__,2).'/bin/console').' app:crm:check --no-debug', $code);
exit($code);
