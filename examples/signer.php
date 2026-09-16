<?php

declare(strict_types=1);

use Filefabrik\Hsap\CertHelper;
use Filefabrik\Hsap\Signer;
use Filefabrik\Hsap\SignerConfiguration;
use GuzzleHttp\Psr7\Request;

require_once dirname(__DIR__).'/vendor/autoload.php';

/**
 * Prepare configuration
 */
$currentDateTime = new DateTime()->getTimestamp();
$configuration   = new SignerConfiguration();
$configuration->withHeader('@method')
    /*
     * check duplicate content-digest
     * ->withHeader('content-digest')
     */

              ->mustSignBody()
              ->withBodySignatureAlgorithm('sha256')
              ->withCreated($currentDateTime)
              ->withExpires($currentDateTime + 7200)
              ->withTag('mastodon')
              ->withNonce('nonce-now')
;

/**
 * Prepare the Request
 */
$bodyParams = ['recipient' => 'Alex', 'amount' => 900000000];
$bodyJson   = json_encode($bodyParams, JSON_THROW_ON_ERROR);
$request    = new Request(method: 'POST', uri: 'https://test', body: $bodyJson);

$signer     = new Signer($request, $configuration);
$theRequest = $signer->sign();

$certInstance = new CertHelper('ec_brainpoolP512t1');

$signerDatas = [
    'data'       => $signer->stringToSign,
    'signature'  => base64_decode($signer->base64_signature),
    'public_key' => $certInstance->getPublicPemContent(),
    'algorithm'  => 'sha512',
];

$verified = openssl_verify(...$signerDatas);
$t        =
    'MIGEAkAcMVgwFZ8W1yFQvYrVmeH4wjJLUMJtvRs+AcZjkZqvaGsEOhYBVXAdU+kRv4V9MB+7yGvqQ/SkGhcdiHJP8gNPAkAMHoT63GF7Zf85Sp/TPHTwBd5uiaO127wrDNKjkS+UAHSt2ta9sTu6LmgvoMwZnD3p4izdpEbmbwgyB02V9Lo/';
$t2       =
    'MIGEAkBluTZ44gAF3hB45yItaIB+gmIgEDLYARctGcG6xhVsHTiWWOT2oXG19fWNFCjTAAI3fiMDuxIR1ZZmS1kiBv3UAkBthGWX4lR7wAsWWegssWM9z+rXlevCN4dc4aKjiAU5UbKaSdtKDKWFTN1aQv48OC8OLNn8uL8WNfBmoHvUN3tb';
echo $verified;

$signerDatas = [
    'data'       => $signer->stringToSign,
    'signature'  =>$t2,
    'public_key' => $certInstance->getPublicPemContent(),
    'algorithm'  => 'sha512',
];

$verified = openssl_verify(...$signerDatas);
echo $verified;
