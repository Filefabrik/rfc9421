<?php

declare(strict_types=1);

use Bakame\Http\StructuredFields\StructuredFieldError;
use Filefabrik\Rfc9421\Cryptography\SigningEnum;
use Filefabrik\Rfc9421\Exceptions\AbstractRfc9421Exception;
use Filefabrik\Rfc9421\Exceptions\AlreadySetException;
use Filefabrik\Rfc9421\Exceptions\ContentDigestException;
use Filefabrik\Rfc9421\Exceptions\EnumTypeException;
use Filefabrik\Rfc9421\Exceptions\SignatureException;
use Filefabrik\Rfc9421\Exceptions\SignatureExtractionException;
use Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException;
use Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException;
use Filefabrik\Rfc9421\RequestSigner;
use Filefabrik\Rfc9421\ResponseSigner;
use Filefabrik\Rfc9421\Signature\DerivedComponentEnum;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421\VerifyPrepare;
use Filefabrik\Rfc9421\VerifyRequest;
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
 * for testing purposes we use an own cert-loader
 */
$certInstance = new CertHelper('rsa');
$targetUrl    = 'http://localhost:8001/path?param=value&foo=bar&baz=bat%2Dman';

$extraParser = ['example-dict' => 'dictionary'];

/*
 * Prepare signingConfiguration
 */
$currentDateTime = new DateTime()->getTimestamp();

try {
    $signingConfiguration = new SigningConfiguration()
        /*
         * content-type will be injected after signing so configure that we are signing manuell the content-type
         */
      //  ->processWithoutExistingHeader('content-type')
        /*
         * the method we are generating the content-type value (simply via header line)
         */
        ->addSigningMethod(
            componentName: 'content-type',
            closure      : fn (RequestInterface $request): string => $request->getHeaderLine('content-type'),
        )
        /*
         * tell the signing process that content-type has to be signed
         */
        ->signHeader('content-type')
        ->signHeader('content-length')
        ->signHeader(DerivedComponentEnum::query)
        ->signHeader(DerivedComponentEnum::method)
        ->signHeader('"@query-param";name="baz"')
        ->signHeader('x-Origin-host')
        ->signHeader('test-header')
        ->signHeader('"example-dict"')
        ->signHeader('"example-dict";sf')
        ->signHeader('"example-dict";key="a"')
        ->signHeader('"example-dict";key="c"')
        ->signHeader('"example-dict";key="d"')
        ->signHeader(DerivedComponentEnum::authority)
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
} catch (AlreadySetException|SignatureException|ContentDigestException|SignatureExtractionException$e) {
    echo $e->getMessage();

    return;
}

/*
 * Make sure to use the same methods in signer and verifier!
 */
$prepareMethods = [
    DerivedComponentEnum::authority->value =>
        fn (RequestInterface $message): string => $message->getHeaderLine('x-Origin-host'),
];
/*
 * Modify derived components generation if needed.
 *
 * don't forget to use thís prepared component in the verifier
 */
$signingConfiguration->addSigningMethods($prepareMethods);
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
} catch (AlreadySetException|SignatureException|ContentDigestException|StructuredFieldError|UnProcessableSignatureException|SignatureExtractionException|EnumTypeException $e) {
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
$verifierConfiguration->getStructuredFields()
                      ->addMany($extraParser)
;
$verifierConfiguration->addSigningMethods($prepareMethods);
$verifyPrepareInstance = new VerifyPrepare($signedRequest, $verifierConfiguration);

/*
 * Prepare the incoming Request for verifying
 * after $verifyPrepareInstance->prepareVerification(); you are able to configure/check/load(i.e. the public pem from the sender) and then you handle the real verify process
 * important, throws if something wrong
 */
try {
    $verifyPrepareInstance->prepareVerification();
} catch (ContentDigestException|VerifyHeaderSignatureException|SignatureException|AlreadySetException|SignatureExtractionException $e) {
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
    $verifier = new VerifyRequest($verifyPrepareInstance);
    try {
        $res = $verifier->verifying();
    } catch (AbstractRfc9421Exception $e) {
        echo 'result false: '.$e->getMessage();

        return;
    }

    echo 'verify result: ', $res, PHP_EOL;

    /*
     * After successfully verifying you have access to verified header
     */
    if ($res) {
        /**
         * here are all the headers they were in the singing process from requester and are validated and valued at this point
         */
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
            echo 'created: '.$metadata->created.PHP_EOL;
        }

        if ($verifier->hasValidatedBody) {
            echo 'Cool, body verified'.PHP_EOL;
            $body = (string)$verifier->getMessage()
                                     ->getBody()
            ;
        } else {
            echo 'body was not part of verifying process'.PHP_EOL;
        }
    }

    //... your internal processing here
    // after processing give back a signed response, that the sender can be sure the response is valid
    $responseBodyParams = ['account' => 'Alex', 'new-amount' => 900000000];
    try {
        $responseBodyJson = json_encode($responseBodyParams, JSON_THROW_ON_ERROR);
    } catch (JsonException $jsonException) {
        echo $jsonException->getMessage();

        return;
    }
    $contentLength = ['content-length' => (string)strlen($responseBodyJson)];
    $response      = new Response(headers: [], body: $responseBodyJson);

    //... now sign the response
    try {
        $responseSigningConfiguration = new SigningConfiguration()
            /*
             * Content Length as a special method
             *
             * The content-length will be created to header by guzzle or something AFTER we are signing the request/response. So we have to calculate the length before.
             * @important, when the receiver of this response verifies this response the `->processWithoutExistingHeaders(['content-length'])` never set.
            */
            //   ->processWithoutExistingHeaders(['content-length'])
            // make sure the receiver Alice expects the following response Key
            ->setSignatureKeyName('responseSig=')
            // singing the status as relevant information for the requester Alice, to ensure the status
            ->signHeader(DerivedComponentEnum::status)
            // todo header exists and must not be set separate as header
            ->signHeader('content-length')
            // the sender of the request expects an algo that Bob can check while receive this signed response
            ->setSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_512)
            // set alice private pem to encrypt the response
            ->setPrivatePem($certInstance->getPrivatPemContent())
            // singing the response body
            ->setContentDigestAlgo('sha256')
        ;
    } catch (AbstractRfc9421Exception $e) {
        echo 'response signing configuration error: '.$e->getMessage();

        return;
    }
    $responseSigner = new ResponseSigner(message: $response, signingConfiguration: $responseSigningConfiguration);
    try {
        $signedResponse = $responseSigner->sign();
    } catch (AbstractRfc9421Exception|StructuredFieldError $e) {
        echo 'response signing error: '.$e->getMessage();

        return;
    }
    echo 'response is successfully signed and can be response to the sender'.PHP_EOL;

    //... next step the receiver of this response verifies this response
    return;
}

echo 'missing key id/PEm';
