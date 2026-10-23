<?php
// Compatibility wrapper; the command runs synthetic operations inside a rolled-back transaction.
passthru(escapeshellarg(PHP_BINARY).' '.escapeshellarg(dirname(__DIR__,2).'/bin/console').' app:crm:check --no-debug', $code);
if($code===0) { passthru(escapeshellarg(PHP_BINARY).' '.escapeshellarg(__DIR__.'/check-chat-form.php'),$code); }
exit($code);
