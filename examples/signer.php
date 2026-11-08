<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

use Filefabrik\Rfc9421\Enums\{DerivedComponentEnum, InitialContentsEnum, SigningEnum};
use Filefabrik\Rfc9421\Exceptions\{AbstractRfc9421Exception, ProcessingException, SignatureExtractionException};
use Filefabrik\Rfc9421\RequestSigner;
use Filefabrik\Rfc9421\ResponseSigner;
use Filefabrik\Rfc9421\Signature\ComponentName;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421\VerifyRequest;
use Filefabrik\Rfc9421\VerifyRequestPrepare;
use Filefabrik\Rfc9421Test\Examples\Tools\JsonToString;
use Filefabrik\Rfc9421Test\Tools\CertHelper;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
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
 * for testing purposes we use our own cert-loader
 */
$certInstance = new CertHelper('rsa');
$targetUrl    = 'http://localhost:8001/path?param=value&foo=bar&baz=bat%2Dman';

/*
 * Prepare signingConfiguration
 */
$currentDateTime = new DateTime()->getTimestamp();
/*
 * Tell later that the HTTP Header Value will be extracted with the Bakame dictionary
 */
$extraParser = ['example-dict' => 'dictionary'];
/*
 * Programmatically tell which components has to be into signing process.
 * This example shows all possible configuration methods.
 * Use ist as you need
 */
try {
    $signingHeaders = [
        /*
         * as string without quotas
         */
        'content-type',
        /*
         * as string with quotas
         */
        '"content-length"',
        /*
         * as Enum
         */
        DerivedComponentEnum::query,
        DerivedComponentEnum::method,
        /*
         * as component-string
         */
        '"@query-param";name="baz"',
        /*
         * programmatically add
         * '"example-dict";sf'
         */
        ComponentName::fromNameAndInitialContent(name: 'example-dict', initialContentsEnum: InitialContentsEnum::sf),
        DerivedComponentEnum::authority,
        ComponentName::fromNameAndInitialContentEnumWithValue(
            name               : 'example-dict',
            initialContentsEnum: InitialContentsEnum::key,
            value              : 'a',
        ),
        /*
         * '"example-dict";key="c"'
         */
        ComponentName::fromNameAndInitialContentEnumWithValue(
            name               : 'example-dict',
            initialContentsEnum: InitialContentsEnum::key,
            value              : 'c',
        ),
        /*
         * '"example-dict";key="d"'
         */
        ComponentName::fromNameAndInitialContentEnumWithValue(
            name               : 'example-dict',
            initialContentsEnum: InitialContentsEnum::key,
            value              : 'd',
        ),
    ];
} catch (SignatureExtractionException $signatureExtractionException) {
    echo $signatureExtractionException->getMessage();

    return;
}

try {
    $signingConfiguration = new SigningConfiguration()
        /*
         * content-type will be injected after signing so configure that we are signing manuell the content-type
         * the method we are generating the content-type value (simply via header line)

        ->addSigningMethod(
            componentName: 'content-type',
            closure      : fn (RequestInterface $request): string => $request->getHeaderLine('content-type'),
        ) */
        /*
         * tell the signing process that content-type has to be signed
         */
        //->signHeader('"@query-param";name="baz"')
        ->mustCreateContentDigest()
        ->signHeader('x-Origin-host')
        ->signHeader('test-header')
        ->signHeader('"example-dict"')
        ->signHeader('content-digest')
        /*
         * Setting a bundle of prepared components
         */
        ->signHeaders(headerKeys: $signingHeaders)
        ->setSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_512)
        ->setCreated($currentDateTime)
        ->setExpires($currentDateTime + 7200)
        ->setTag('mastodon')
        ->setKeyid('http://127.0.0.1:8001/account/publicPem#main-key')
        ->withNonce('nonce-now')
        ->setContentDigestAlgo('sha256')
        // content digest signer key
        ->setSignatureKeyName('sig=')
        // for signing needs private
        ->setPrivatePem($certInstance->getPrivatPemContent())
    ;
    $signingConfiguration->getStructuredFields()
                         ->addMany($extraParser)
    ;
} catch (AbstractRfc9421Exception$rfc9421Exception) {
    echo $rfc9421Exception->getMessage();

    return;
}

/*
 * We take the authority from the x-origin HTTP Header
 * Make sure to use the same methods in signer and verifier!
 */
$prepareMethods = [
    /*
     * @authority
     */
    DerivedComponentEnum::authority->value =>
    /*
     * Method/Closure to take the value from header
     */
        fn (RequestInterface $message): string => $message->getHeaderLine('x-Origin-host'),
];
/*
 * Modify derived components generation if needed.
 *
 * don't forget to use thís prepared component in the verifier
 */
$signingConfiguration->addSigningMethods($prepareMethods);
/*
 * Prepare the Request
 */

$bodyJson = JsonToString::toString(['recipient' => 'Alex', 'add' => 90.09, 'currency' => 'EUR']);

$request = new Request(
    method : 'POST',
    uri    : $targetUrl,
    headers: [
        'x-origin-host' => 'mytest.com',
        'content-type'  => 'application/json',
        'test-header'   => 'playmobile',
        'Example-Dict'  => '  a=1,    b=2;x=1;y=2,   c=(a   b   c), d ',
    ],
    body   : $bodyJson,
);

$signer = new RequestSigner(message: $request, signingConfiguration: $signingConfiguration);
try {
    $signedRequest = $signer->sign();
} catch (AbstractRfc9421Exception $rfc9421Exception) {
    echo $rfc9421Exception->getMessage();

    return;
}

/**
 * Simulate the incoming Request while using the signed request
 * Now decrypt the Request
 */
$verifierConfiguration = new SigningConfiguration();
$verifierConfiguration->getStructuredFields()
                      ->addMany($extraParser)
;
$verifierConfiguration->addSigningMethods($prepareMethods);
$verifyPrepareInstance = new VerifyRequestPrepare($signedRequest, $verifierConfiguration);

/*
 * Prepare the incoming Request for verifying
 * after $verifyPrepareInstance->prepareVerification(); you are able to configure/check/load(i.e. the public pem from the sender) and then you handle the real verify process
 * important, throws if something wrong
 */
try {
    $verifyPrepareInstance->prepareVerification();
} catch (AbstractRfc9421Exception $rfc9421Exception) {
    echo $rfc9421Exception->getMessage();

    return;
}

/*
 * with the keyId you should be able to download the public pem
 */
$keyId = $verifierConfiguration->metadata->keyid;

if (null === $keyId) {
    echo 'missing key id/PEm';

    return;
}

$verifierConfiguration->setPublicPem(publicPem: $certInstance->getPublicPemContent());

/*
 * When using mastodon it does not request in the metadata the signature algorithm
 * The only supported algorithm is still RSASSA-PKCS1-v1_5 Using SHA-256
 * $verifierConfiguration->setSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_256);
 *
 * $verifierConfiguration->setContentDigestAlgo(DigestAlgorithmsEnum::sha256);
 */

/**
 * Verify the incoming request from above(receiver-side)
 */
$VerifyRequest = new VerifyRequest($verifyPrepareInstance);
try {
    $verifiedRequestBool = $VerifyRequest->verifying();
} catch (AbstractRfc9421Exception $rfc9421Exception) {
    echo 'result false: '.$rfc9421Exception->getMessage();

    return;
}

echo 'verify result: ', $verifiedRequestBool, PHP_EOL;

if (!$verifiedRequestBool) {
    return;
}/*
 * After successfully verifying you have access to HTTP Header Lines/Parts
 * they were in the singing process from requester and are validated and valued at this Point
 */
echo PHP_EOL.'signing params lines with there values:'.PHP_EOL;
try {
    echo implode(
        "\n",
        $VerifyRequest->getSignedHeaderLines()
                      ->values(),
    );
} catch (ProcessingException $processingException) {
    echo $processingException->getMessage();

    return;
}

/*
 * Also the $metadata are verified
 */
$metadata = $VerifyRequest->getSigningConfiguration()->metadata;

/*
 * or something
 */
if (null !== $metadata->created && ($currentDateTime + 6000) > $metadata->created) {
    // ...
    echo 'has created date and created date is in range: '.$metadata->created.PHP_EOL;
}

if ($VerifyRequest->hasValidatedBody) {
    echo 'Cool, the body was in the requester signing process and is verified:'.PHP_EOL;
    $body = (string)$VerifyRequest->getMessage()
                                  ->getBody()
    ;
    echo $body.PHP_EOL;
} else {
    echo 'body was not part of verifying process'.PHP_EOL;
}

/*
 * Let us start a signed response
 */

// after processing give back a signed response, that the sender can be sure the response is valid

$responseBodyJson = JsonToString::toString(['account' => 'Alex', 'new-amount' => 1290.09, 'currency' => 'EUR']);
$response         = new Response(headers: [], body: $responseBodyJson);

//... now sign the response
// todo also add ;req parameters
try {
    $responseSigningConfiguration = new SigningConfiguration()
        // make sure the receiver Alice expects the following response Key
        ->setSignatureKeyName('responseSig=')
        // singing the status as relevant information for the requester Alice, to ensure the status
        ->signHeader(DerivedComponentEnum::status)
        // Content-Length has a default method to calculate the length of the body
        ->signHeader('content-length')
        // the sender of the request expects an algo that Bob can check while receive this signed response
        // todo use different signing for response
        ->setSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_512)
        // set alice private pem to encrypt the response
        ->setPrivatePem($certInstance->getPrivatPemContent())
        // singing the response body
        // todo use different signing for response
        ->setContentDigestAlgo('sha256')
    ;
} catch (AbstractRfc9421Exception $rfc9421Exception) {
    echo 'response signing configuration error: '.$rfc9421Exception->getMessage();

    return;
}

/**
 * Signing the Response if need signed response
 */
$responseSigner = new ResponseSigner(message: $response, signingConfiguration: $responseSigningConfiguration);
try {
    $signedResponse = $responseSigner->sign();
} catch (AbstractRfc9421Exception $rfc9421Exception) {
    echo 'response signing error: '.$rfc9421Exception->getMessage();

    return;
}

echo 'response is successfully signed and can be response to the sender'.PHP_EOL;
