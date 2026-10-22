<?php
$root=dirname(__DIR__,2).'/public';
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
$file=realpath($root.rawurldecode($path));
if ($file && str_starts_with($file,$root.'/') && is_file($file) && !str_ends_with($file,'.php')) { return false; }
require $root.'/index.php';
