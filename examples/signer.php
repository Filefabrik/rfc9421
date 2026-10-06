<?php

declare(strict_types=1);

use Filefabrik\Rfc9421\Cryptography\SigningEnum;
use Filefabrik\Rfc9421\Signer;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421\Verify;
use Filefabrik\Rfc9421Test\Tools\CertHelper;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\MessageInterface;

require_once dirname(__DIR__).'/vendor/autoload.php';
/**
 * use openssl (native php extension) which so preconfigured
 * if you need another library oar need some modifications, build your own
 */
require_once dirname(__DIR__).'/src/register-openssl-signing-verify.php';
/**
 * use phpseclib4 library which is preconfigured
 * if you need another library oar need some modifications, build your own
 */
#require_once dirname(__DIR__).'/src/register-phpseclib4-signing-verify.php';

/**
 * for testing purposes we use a own cert-loader
 */
$certInstance = new CertHelper('rsa');
#$certInstance = new CertHelper('hmac');
#$certInstance = new CertHelper('ed25519');
$targetUrl = 'http://localhost:8001/mastodon';
/**
 * Prepare signingConfiguration
 */
$currentDateTime = new DateTime()->getTimestamp();

$signingConfiguration = new SigningConfiguration()
    ->signHeader('@method')
    ->signHeader('x-Origin-host')
    ->withSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_512)
    // ->withSigningAlgorithm(SigningEnum::hmac_sha_256)
    ->withCreated($currentDateTime)
    ->withExpires($currentDateTime + 7200)
    ->withTag('mastodon')
    ->withKeyid('http://127.0.0.1:8001/actor#main-key')
    ->withNonce('nonce-now')
    ->setContentDigestHashAlgo('sha512')
    // content digest signer key
    ->withSignatureKeyName('testValue=')
    // for signing needs private
    ->setPrivatePem($certInstance->getPrivatPemContent())
;

/**
 * Modify derived components generation if needed.
 *
 * don't forget to use thís prepared component in the verifier
 */
$signingConfiguration->withPrepareComponentsMethod(
    componentName: '@authority',
    callable     : fn(MessageInterface $message) => $message->getHeaderLine('x-Origin-host'),
);
/**
 * Prepare the Request
 */
$bodyParams = ['recipient' => 'Alex', 'amount' => 900000000, 'text' => 'fdawsfs'];
$bodyJson   = json_encode($bodyParams, JSON_THROW_ON_ERROR);
$request    = new Request(
    method : 'POST',
    uri    : $targetUrl,
    headers: ['x-origin-host' => 'mytest.com'],
    body   : $bodyJson,
);

$signer        = new Signer(message: $request, signingConfiguration: $signingConfiguration);
$signedRequest = $signer->sign();
/**
 * working with local server
 */
/**
 * @noinspection \PhpFullyQualifiedNameUsageInspection
 * $response = new \GuzzleHttp\Client()->send($signedRequest);
 */
/**
 * Now decrypt the Request
 */
$verifierConfiguration = new SigningConfiguration();

/**
 * Verify the generated request from above(receiver-side)
 */
$verifier = new Verify(message: $signedRequest, signingConfiguration: $verifierConfiguration);
/**
 * Prepare the verify to extract for mastodon the sender public pem location
 */
$verifier->prepareVerify();
/**
 * with the keyId you should be able to download the public pem
 */
$keyId = $verifierConfiguration->metadata->keyid;

if ($keyId) {
    $verifierConfiguration->setPublicPem(publicPem: $certInstance->getPublicPemContent());

    /*
     * // when using mastodon it does not request in the metadata the signature algorithm
     * The only supported algorithm is still RSASSA-PKCS1-v1_5 Using SHA-256
     */
    /*
     * $verifierConfiguration->withSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_256);
     */
    /*
     *  todo check for mastodon
     * $verifierConfiguration->setContentDigestHashAlgo(DigestAlgorithmsEnum::sha256);
     */

    $res = $verifier->verifying();
    echo 'verify result: ', $res, "\n";

    /**
     * After successfully verifying you have access to verified header
     */

    if ($res) {
        $params = $verifier->validSignedParameters;

        /*
         * Also the $metadata are verified
         */
        $metadata = $verifier->signingConfiguration->metadata;
        // in example: check created or expire

        if ($metadata->created) {
            // ...
        }
        $body = (string)$verifier->message->getBody();
    }

    return;
}
echo 'missing key id/PEm';
