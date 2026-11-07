<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

use Filefabrik\Rfc9421Test\Tools\CertHelper;

require_once __DIR__.'/../../vendor/autoload.php';
/*
 * use openssl (native php extension) which so preconfigured
 * if you need another library oar need some modifications, build your own
 */
require_once __DIR__.'/../../src/register-openssl-signing-verify.php';
/*
 * which account/server side we are serving
 */
global $serverContext;
/**
 * Logging display
 */
require_once __DIR__.'/account_bootstrap.php';

function response404(): void
{
    header('HTTP/1.1 404 Not Found');
    echo '<h1>Not Found in $serverContext</h1>';
    die();
}

$request         = [
    'host'   => $_SERVER['SERVER_NAME'],
    'method' => $_SERVER['REQUEST_METHOD'],
];
$scriptServerDir = __DIR__.'/'.$serverContext;
$sPath           = $_SERVER['SCRIPT_NAME'];

$route = null;
if ('/public-pem' === $sPath) {
    $certInstance = new CertHelper('rsa', __DIR__.'/'.$serverContext.'/Keys/');
    echo $certInstance->getPublicPemContent();
    exit(0);
}
if (str_starts_with(''.$sPath, '/accounts')) {
    $route = $scriptServerDir.$sPath.'.php';
}

if (null === $route) {
    $route = match ($sPath) {
        '/.well-known/webfinger' => $scriptServerDir.'/webfinger/response.php',
        default                  => $scriptServerDir.'/404.php',
    };
}

if (file_exists($route)) {
    /* @noinspection ForgottenDebugOutputInspection */
    print_r($route);
    include $route;
    exit(0);
}

response404();
