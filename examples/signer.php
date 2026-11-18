<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

use Filefabrik\Rfc9421\Enums\{DerivedComponentEnum, SigningEnum};
use Filefabrik\Rfc9421\Exceptions\{AbstractRfc9421Exception,
    AlreadySetException,
    ContentDigestException,
    EmptyException,
    EnumTypeException,
    InvalidHeaderKeyException,
    MethodException,
    PrepareMethodsException,
    ProcessingException,
    SignatureException,
    SignatureExtractionException,
    StructuredFieldException,
    UnexpectedValue,
    VerifyHeaderSignatureException
};
use Filefabrik\Rfc9421\RequestSigner;
use Filefabrik\Rfc9421\ResponseSigner;
use Filefabrik\Rfc9421\Signer\{Signatures\ReadLines, Signatures\WriteLines};
use Filefabrik\Rfc9421\SigningComponents;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421\VerifyRequest;
use Filefabrik\Rfc9421\VerifyRequestPrepare;
use Filefabrik\Rfc9421\VerifyResponse;
use Filefabrik\Rfc9421\VerifyResponsePrepare;
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
$signerCertInstance = new CertHelper(name: 'rsa');
$targetUrl          = 'http://localhost:8001/path?param=value&foo=bar&baz=bat%2Dman';

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
    $signRequestComponents = new SigningComponents()
        /*
            * as string without quotas
            */

        ->sign(headerKey: 'content-type')
        /*
         * as string with quotas
         */
        ->sign(headerKey: '"content-length"')
        /*
        * as Enum
        */
        ->sign(headerKey: DerivedComponentEnum::query)
        ->sign(headerKey: DerivedComponentEnum::method)
        ->sign(headerKey: DerivedComponentEnum::authority)
        /*
        * with the name
        */
        ->signWithName(headerKey: '@query-param', nameValue: 'baz')
        /*
       * programmatically add
       * '"example-dict";sf'
       */
        ->signWithSF(headerKey: 'example-dict')
        ->signWithKey(headerKey: 'example-dict', keyValue: 'a')
        /*
    * '"example-dict";key="c"'
    */
        ->signWithKey(headerKey: 'example-dict', keyValue: 'c')
        /*
      * '"example-dict";key="d"'
      */
        ->signWithKey(headerKey: 'example-dict', keyValue: 'd')
        ->sign('x-Origin-host')
        ->sign('test-header')
        ->sign('example-dict')
    ;
} catch (AlreadySetException|SignatureExtractionException $e) {
    echo $e->getMessage();

    return;
}

try {
    $signingConfiguration = new SigningConfiguration()
        /*
         * Setting a bundle of prepared components
         */
        ->setSigningComponents($signRequestComponents)
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
        ->withContentDigest(contentDigestAlgo: 'sha256')
        ->setSigningAlgorithm(algorithm: SigningEnum::rsa_v_1_5_sha_512)
        ->setCreated(created: $currentDateTime)
        ->setExpires(expires: $currentDateTime + 7200)
        ->setTag(tag: 'mastodon')
        ->setKeyid(keyid: 'http://127.0.0.1:8001/account/publicPem#main-key')
        ->withNonce(nonce: 'nonce-now')
        // content digest signer key
        ->setSignatureKeyName(signatureKey: 'sig=')
        // for signing needs private
        ->setPrivatePem(privatePem: $signerCertInstance->getPrivatPemContent())
    ;
    $signingConfiguration->getStructuredFields()
                         ->addMany(fields: $extraParser)
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
$signingConfiguration->addSigningMethods(methods: $prepareMethods);
/*
 * Prepare the Request
 */

$bodyJson = JsonToString::toString(data: ['recipient' => 'Alex', 'add' => 90.09, 'currency' => 'EUR']);

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
                      ->addMany(fields: $extraParser)
;

/*
 * on recipient-side of the request you can also check, that all you are expecting signed components was signed by the sender
 */
$verifierConfiguration->addSigningMethods(methods: $prepareMethods);
$verifyPrepareInstance = new VerifyRequestPrepare(
    message             : $signedRequest,
    signingConfiguration: $verifierConfiguration,
);

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
$keyId = $verifierConfiguration->getMetadata()->keyid;

if (null === $keyId) {
    echo 'missing key id/PEm';

    return;
}

try {
    $verifierConfiguration->setPublicPem(publicPem: $signerCertInstance->getPublicPemContent());
} catch (UnexpectedValue $unexpectedValue) {
    echo $unexpectedValue->getMessage();

    return;
}

/*
 * When using mastodon it does not request in the metadata the signature algorithm
 * The only supported algorithm is still RSASSA-PKCS1-v1_5 Using SHA-256
 * $verifierConfiguration->setSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_256);
 *
 * $verifierConfiguration->withContentDigest(DigestAlgorithmsEnum::sha256);
 */

/**
 * Verify the incoming request from above(receiver-side)
 */
$VerifyRequest = new VerifyRequest(preparedVerify: $verifyPrepareInstance);
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
        separator: "\n",
        array    : $VerifyRequest->getSignedHeaderLines()
                                 ->values(),
    );
} catch (ProcessingException $processingException) {
    echo $processingException->getMessage();

    return;
}

/*
 * Also the $metadata are verified
 */
$metadata = $VerifyRequest->getSigningConfiguration()
                          ->getMetadata()
;

/*
 * or something
 */
if (null !== $metadata->getCreated() && ($currentDateTime + 6000) > $metadata->getCreated()) {
    // ...
    echo 'has created date and created date is in range: '.$metadata->getCreated().PHP_EOL;
}

try {
    if ($VerifyRequest->hasSignedBody()) {
        echo 'Cool, the body was in the requester signing process and is verified:'.PHP_EOL;
        $body = (string)$VerifyRequest->getMessage()
                                      ->getBody()
        ;
        echo $body.PHP_EOL;
    } else {
        echo '!!!! Body was not part of verifying process'.PHP_EOL;
    }
} catch (ProcessingException $processingException) {
    echo $processingException->getMessage();

    return;
}

/*
 * Let us start a signed response
 */

// after processing give back a signed response, that the sender can be sure the response is valid

$responseBodyJson = JsonToString::toString(data: ['account' => 'Alex', 'new-amount' => 1290.09, 'currency' => 'EUR']);
$response         = new Response(headers: [], body: $responseBodyJson);
try {
    $responseComponents = new SigningComponents()
        // singing the status as relevant information for the requester Alice, to ensure the status
        ->sign(headerKey: DerivedComponentEnum::status)
        // content digest which must be response to the requester that the requester can check, the response body was not tempered
        ->sign(headerKey: 'content-digest')
        /*
         * Signing ";asReq"
         */
        ->sign(headerKey: 'content-digest', asReq: true)
        // Content-Length has a default method to calculate the length of the body
        ->sign(headerKey: 'content-length')
    ;
} catch (AlreadySetException|SignatureExtractionException $e) {
    echo $e->getMessage();

    return;
}

//... now sign the response
// todo also add ;asReq parameters
try {
    $responseSigningConfiguration = new SigningConfiguration()
        ->setSigningComponents($responseComponents)
        // make sure the receiver Alice expects the following response Key
        ->setSignatureKeyName(signatureKey: 'responseSig=')
        ->withContentDigest(contentDigestAlgo: 'sha256')

        // the sender of the request expects an algo that Bob can check while receive this signed response
        // todo use different signing for response
        ->setSigningAlgorithm(algorithm: SigningEnum::rsa_v_1_5_sha_512)
        // set alice private pem to encrypt the response
        ->setPrivatePem(privatePem: $signerCertInstance->getPrivatPemContent())
        // singing the response body
        // todo use different signing for response
        //->setContentDigestAlgo(contentDigestAlgo: 'sha256')
    ;
} catch (AbstractRfc9421Exception $rfc9421Exception) {
    echo 'response signing configuration error: '.$rfc9421Exception->getMessage();

    return;
}

/*
 * Signing the Response if need signed response
 */
try {
    $responseSigner = new ResponseSigner(
        message             : $response,
        signingConfiguration: $responseSigningConfiguration,
        expectedSignedLines : $VerifyRequest->getSignedHeaderLines(),
    );
} catch (ProcessingException $processingException) {
    echo $processingException->getLine(), $processingException->getMessage();

    return;
}

try {
    $signedResponse = $responseSigner->sign();
} catch (AbstractRfc9421Exception $rfc9421Exception) {
    echo 'response signing error: '.$rfc9421Exception->getMessage();

    return;
}

echo 'response is successfully signed and can be response to the sender'.PHP_EOL;

echo 'Start Verify the Signed Response'.PHP_EOL;
try {
    $responseSigningComponents = new SigningComponents()->sign(headerKey: 'content-digest');
} catch (AlreadySetException|SignatureExtractionException $e) {
    echo 'response signing configuration error: '.$e->getMessage();

    return;
}

try {
    $verifyResponseConfiguration = new SigningConfiguration()
        ->setSigningComponents($responseSigningComponents)
        ->setPublicPem($signerCertInstance->getPublicPemContent())
    ;
} catch (AbstractRfc9421Exception $rfc9421Exception) {
    echo $rfc9421Exception->getMessage(), PHP_EOL;

    return;
}

/**
 * We expect a signed response with the `"content-digest";asReq` so we have to use it in the verification process
 * todo make an object for rebuilding expectation more elegant then the read lines
 */
$wl = new WriteLines();
try {
    $wl->fromSignatureLine(line: '"content-digest": sha-256=:qvoLOe+II0NxvDyMNg80z+xDv6aD9IkT9w8a340Q1Oo=:');
} catch (SignatureExtractionException $signatureExtractionException) {
    echo $signatureExtractionException->getMessage(), PHP_EOL;

    return;
}

$resRL                           = new ReadLines(lines: $wl);
$signedResponseVerifyPreparation = new VerifyResponsePrepare(
    message             : $signedResponse,
    signingConfiguration: $verifyResponseConfiguration,
)
    // important feature if we have ';asReq' otherwise it will not work
    ->setExpectedReqSigners(
        expectedSignedLines: $resRL,
    )
;
try {
    $signedResponseVerifyPreparation->prepareVerification();
} catch (AbstractRfc9421Exception $rfc9421Exception) {
    echo $rfc9421Exception->getMessage();

    return;
}

echo 'after prepare verifying response start the Verify Process', PHP_EOL;

$signedResponseVerify = new VerifyResponse(preparedVerify: $signedResponseVerifyPreparation);

try {
    $verifySignedResponseResult = $signedResponseVerify->verifying();
} catch (ContentDigestException|VerifyHeaderSignatureException|UnexpectedValue|StructuredFieldException|SignatureExtractionException|SignatureException|ProcessingException|PrepareMethodsException|MethodException|InvalidHeaderKeyException|EnumTypeException|EmptyException $e) {
    echo 'bug during the signed response verify process:', PHP_EOL, $e->getMessage();

    return;
}

echo 'after verify response ', PHP_EOL;
echo 'verify signed Response Verification Result: ', $verifySignedResponseResult, PHP_EOL;
