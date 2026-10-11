<?php

declare(strict_types=1);

use Filefabrik\Rfc9421\Cryptography\SigningEnum;
use Filefabrik\Rfc9421\Exceptions\AbstractRfc9421Exception;
use Filefabrik\Rfc9421\Exceptions\AlreadySetException;
use Filefabrik\Rfc9421\Exceptions\ContentDigestException;
use Filefabrik\Rfc9421\Exceptions\InvalidDerivedKeyException;
use Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException;
use Filefabrik\Rfc9421\Exceptions\SignatureException;
use Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException;
use Filefabrik\Rfc9421\Signer;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421\Verify;
use Filefabrik\Rfc9421\VerifyPrepare;
use Filefabrik\Rfc9421Test\Tools\CertHelper;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;

require_once __DIR__.'/../vendor/autoload.php';
/*
 * use openssl (native php extension) which so preconfigured
 * if you need another library oar need some modifications, build your own
 */
require_once __DIR__.'/../src/register-openssl-signing-verify.php';
/*
 * use phpseclib4 library which is preconfigured
 * if you need another library oar need some modifications, build your own
 */
#require_once dirname(__DIR__).'/src/register-phpseclib4-signing-verify.php';

/*
 * for testing purposes we use an own cert-loader
 */
$certInstance = new CertHelper('rsa');
$targetUrl    = 'http://localhost:8001/mastodon';

/*
 * Prepare signingConfiguration
 */
$currentDateTime = new DateTime()->getTimestamp();

try {
    $signingConfiguration = new SigningConfiguration()
        ->signHeader('@method')
        ->signHeader('x-Origin-host')
        ->signHeader('@authority')
        ->setSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_512)
        ->setCreated($currentDateTime)
        ->setExpires($currentDateTime + 7200)
        ->setTag('mastodon')
        ->setKeyid('http://127.0.0.1:8001/actor#main-key')
        ->withNonce('nonce-now')
        ->setContentDigestAlgo('sha256')
        // content digest signer key
        ->setSignatureKeyName('sig=')
        // for signing needs private
        ->setPrivatePem($certInstance->getPrivatPemContent())
    ;
} catch (AlreadySetException|SignatureException|InvalidHeaderKeyException|InvalidDerivedKeyException|ContentDigestException $e) {
    echo $e->getMessage();

    return;
}

/*
 * Make sure to use the same methods in signer and verifier!
 */
$prepareMethods = [
    '@authority' =>
        fn (RequestInterface $message): string => $message->getHeaderLine('x-Origin-host'),
];
/*
 * Modify derived components generation if needed.
 *
 * don't forget to use thís prepared component in the verifier
 */
$signingConfiguration->withPrepareComponentsMethods($prepareMethods);
/**
 * Prepare the Request
 */
$bodyParams = ['recipient' => 'Alex', 'amount' => 900000000, 'text' => 'some text'];
try {
    $bodyJson = json_encode($bodyParams, JSON_THROW_ON_ERROR);
} catch (JsonException $jsonException) {
    echo $jsonException->getMessage();

    return;
}

$request = new Request(
    method : 'POST',
    uri    : $targetUrl,
    headers: ['x-origin-host' => 'mytest.com'],
    body   : $bodyJson,
);

$signer = new Signer(message: $request, signingConfiguration: $signingConfiguration);
try {
    $signedRequest = $signer->sign();
} catch (AlreadySetException|SignatureException|InvalidHeaderKeyException|InvalidDerivedKeyException|ContentDigestException $e) {
    echo $e->getMessage();

    return;
}

/*
 * @noinspection \PhpFullyQualifiedNameUsageInspection
 * $response = new \GuzzleHttp\Client()->send($signedRequest);
 */
/**
 * Now decrypt the Request
 */
$verifierConfiguration = new SigningConfiguration();
$verifierConfiguration->withPrepareComponentsMethods($prepareMethods);
$verifyPrepareInstance = new VerifyPrepare($signedRequest, $verifierConfiguration);

/*
 * Prepare the incoming Request for verifying
 * after $verifyPrepareInstance->prepareVerification(); you are able to configure/check/load(i.e. the public pem from the sender) and then you handle the real verify process
 * important, throws if something wrong
 */
try {
    $verifyPrepareInstance->prepareVerification();
} catch (ContentDigestException|VerifyHeaderSignatureException|SignatureException $e) {
    echo $e->getMessage();

    return;
}

/*
 * with the keyId you should be able to download the public pem
 */
$keyId = $verifierConfiguration->metadata->keyid;

if (null !== $keyId) {
    $verifierConfiguration->setPublicPem(publicPem: $certInstance->getPublicPemContent());

    /*
     * When using mastodon it does not request in the metadata the signature algorithm
     * The only supported algorithm is still RSASSA-PKCS1-v1_5 Using SHA-256
     */
    /*
     * $verifierConfiguration->setSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_256);
     */
    /*
     * todo check for mastodon
     * $verifierConfiguration->setContentDigestAlgo(DigestAlgorithmsEnum::sha256);
     */

    /**
     * Verify the generated request from above(receiver-side)
     */
    $verifier = new Verify($verifyPrepareInstance);
    try {
        $res = $verifier->verifying();
    } catch (AbstractRfc9421Exception $e) {
        echo 'result false: '.$e->getMessage();

        return;
    }

    echo 'verify result: ', $res, "\n";

    /*
     * After successfully verifying you have access to verified header
     */

    if ($res) {
        $signedHeaderLines = $verifier->signedHeaderLines;

        /*
         * Also the $metadata are verified
         */
        $metadata = $verifier->signingConfiguration->metadata;

        /*
         * or something
         */
        if (null !== $metadata->created && ($currentDateTime + 6000) > $metadata->created) {
            // ...
            echo 'created: '.$metadata->created."\n";
        }

        if ($verifier->hasValidatedBody) {
            echo 'Cool, body verified'."\n";
            $body = (string)$verifier->getMessage()
                                     ->getBody()
            ;
        } else {
            echo 'body was not part of verifying process'."\n";
        }
    }

    return;
}

echo 'missing key id/PEm';
