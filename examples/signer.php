<?php

declare(strict_types=1);

use Filefabrik\Hsap\Cryptography\SigningEnum;
use Filefabrik\Hsap\Signer;
use Filefabrik\Hsap\SigningConfiguration;
use Filefabrik\Hsap\Verify;
use Filefabrik\HsapTest\Tools\CertHelper;
use GuzzleHttp\Psr7\Request;

require_once dirname(__DIR__).'/vendor/autoload.php';

require_once dirname(__DIR__).'/tests/Tools/bootstrap-signers.php';

$certInstance = new CertHelper('rsa512');
$signerAlgo   = SigningEnum::rsa_v_1_5_sha_512->name;
/**
 * Prepare signingConfiguration
 */
$currentDateTime = new DateTime()->getTimestamp();
$configuration   = new SigningConfiguration();
$configuration->withHeader('@method')
              ->withHeader('x-Origins-host')
              ->withSigningAlgorithm($signerAlgo)
              ->withCreated($currentDateTime)
              ->withExpires($currentDateTime + 7200)
              ->withTag('mastodon')
              ->withNonce('nonce-now')
    // content digest signer
    //  ->setContentDigestHashAlgo(DigestAlgorithmsEnum::sha256)
              ->withSignatureKeyName('testValue=')
    // for signing needs private
              ->setPrivatePem($certInstance->getPrivatPemContent())
;

/**
 * Prepare the Request
 */
$bodyParams = ['recipient' => 'Alex', 'amount' => 900000000, 'text' => 'fdawsfs'];
$bodyJson   = json_encode($bodyParams, JSON_THROW_ON_ERROR);
$request    = new Request(
    method : 'POST',
    uri    : 'https://test',
    headers: ['x-origin-host' => 'mytest.com'],
    body   : $bodyJson,
);

$signer        = new Signer($request, $configuration);
$signedRequest = $signer->sign();

/**
 * Now decrypt the Request
 */
$verifierConfiguration = new SigningConfiguration()->setPublicPem(publicPem: $certInstance->getPublicPemContent());

/**
 * Verify the generated request from above(receiver-side)
 */
$verifier = new Verify(message: $signedRequest, signingConfiguration: $verifierConfiguration);
echo $verifier->verifying();
