<?php

use Illuminate\Contracts\Console\Kernel;

define('LARAVEL_START', microtime(true));

require dirname(__DIR__).'/vendor/autoload.php';

$app = require_once dirname(__DIR__).'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$status = $kernel->call('lifers:shared-hosting-tick');

fwrite($status === 0 ? STDOUT : STDERR, $kernel->output());

exit($status);
