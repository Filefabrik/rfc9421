<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */

$standaloneDir = dirname(__DIR__).'/vendor/autoload.php';
if (file_exists($standaloneDir)) {
    require $standaloneDir;
} else {
    // testing in app-packages in laravel
    $inParent = dirname(__DIR__, 2).'/vendor/autoload.php';
    if (file_exists($inParent)) {
        require $inParent;
    }
}

if (str_starts_with(php_uname(), 'Windows')) {
    throw new RuntimeException('Does not work on Windows machines');
}
require_once __DIR__.'/account_bootstrap.php';
$webservers = [
    'alice'   => [
        'host' => 'localhost',
        'port' => 8000,
    ],
    'bob' => [
        'host' => 'localhost',
        'port' => 8001,
    ],
];
echo PHP_EOL;
// Starts web servers
foreach ($webservers as $name => $config) {
    extract($config);

    // Starts webserver
    $command = sprintf(
        'php -d xdebug.profiler_enable=On -S %s:%d %s >/dev/null 2>&1 & echo $!',
        $host,
        $port,
        __DIR__."/{$name}/router.php",
    );

    // Execute the command and store the process ID
    $output = [];
    exec($command, $output);
    $pid = (int)$output[0];

    echo sprintf(
        '%s - %s web server started on %s:%d with PID %d',
        date('r'),
        $name,
        $host,
        $port,
        $pid,
    ).PHP_EOL;

    $webservers[$name]['pid'] = $pid;
}
echo PHP_EOL;
echo 'target-scripts here:' , PHP_EOL, PHP_EOL;

// Kill web servers when the process ends
register_shutdown_function(function () use ($webservers) {

    echo  PHP_EOL,PHP_EOL, 'end target-scripts', PHP_EOL;
    foreach ($webservers as $name => $config) {
        extract($config);
        echo sprintf(
            '%s - Killing %s web server process with ID %d',
            date('r'),
            $name,
            $pid,
        ).PHP_EOL;
        exec('kill '.$pid);
    }
});
