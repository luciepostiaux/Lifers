<?php

use Illuminate\Contracts\Console\Kernel;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit(1);
}

define('LARAVEL_START', microtime(true));

require dirname(__DIR__).'/vendor/autoload.php';

$app = require_once dirname(__DIR__).'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

if (! $app->environment('production')) {
    fwrite(STDERR, "Initialisation refusée : APP_ENV doit valoir production.\n");
    exit(1);
}

if (! config('hosting.bootstrap_enabled')) {
    fwrite(STDERR, "Initialisation désactivée : activez temporairement LIFERS_HOSTING_BOOTSTRAP_ENABLED.\n");
    exit(1);
}

$steps = [
    'migrate' => ['--force' => true],
    'db:seed' => ['--force' => true],
    'storage:link' => ['--force' => true, '--relative' => true],
];

foreach ($steps as $command => $parameters) {
    fwrite(STDOUT, "\n## {$command}\n");
    $status = $kernel->call($command, $parameters);
    $output = $kernel->output();

    if ($output !== '') {
        fwrite($status === 0 ? STDOUT : STDERR, $output);
    }

    if ($status !== 0) {
        fwrite(STDERR, "L'initialisation s'est arrêtée pendant {$command}.\n");
        exit($status);
    }
}

fwrite(STDOUT, <<<'MESSAGE'

Initialisation terminée.
Désactivez maintenant LIFERS_HOSTING_BOOTSTRAP_ENABLED et supprimez la tâche ponctuelle dans OVH.

MESSAGE);

exit(0);
