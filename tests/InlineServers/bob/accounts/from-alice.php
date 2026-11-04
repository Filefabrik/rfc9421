<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

use Filefabrik\Rfc9421\Exceptions\AbstractRfc9421Exception;
use Filefabrik\Rfc9421\Exceptions\AlreadySetException;
use Filefabrik\Rfc9421\Exceptions\ContentDigestException;
use Filefabrik\Rfc9421\Exceptions\SignatureException;
use Filefabrik\Rfc9421\Exceptions\SignatureExtractionException;
use Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421\VerifyPrepare;
use Filefabrik\Rfc9421\VerifyRequest;
use Filefabrik\Rfc9421Test\Tools\CertHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\RequestInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Request;

displayLogger('', 2);
displayLogger('Here is Bob\'s Server', 2);

// Specify the URL and data
$url = 'http://localhost:8001/accounts/from-alice';

$bobCertInstance = new CertHelper('rsa', __DIR__.'/../Keys/');

$currentDateTime = new DateTime()->getTimestamp();
/*
 * todo make req for back signing from request
 */
/*
 * Make the HTTP Request into PSR-7 Request for php usage
 */
$fromGlobals = Request::createFromGlobals();

$psr17Factory   = new Psr17Factory();
$psrHttpFactory = new PsrHttpFactory(
    serverRequestFactory: $psr17Factory,
    streamFactory       : $psr17Factory,
    uploadedFileFactory : $psr17Factory,
    responseFactory     : $psr17Factory,
);
$psr7Request    = $psrHttpFactory->createRequest(symfonyRequest: $fromGlobals);

$prepareMethods        = [
    '@authority' => fn (RequestInterface $message): string => $message->getHeaderLine('x-Origin-host'),
];
$verifierConfiguration = new SigningConfiguration()->addSigningMethods($prepareMethods);
$verifyPrepareInstance = new VerifyPrepare(message: $psr7Request, signingConfiguration: $verifierConfiguration);

/*
 * Prepare the incoming Request for verifying
 * after $verifyPrepareInstance->prepareVerification(); you are able to configure/check/load(i.e. the public pem from the sender) and then you handle the real verify process
 * important, throws if something wrong
 */
try {
    $verifyPrepareInstance->prepareVerification();
} catch (ContentDigestException|VerifyHeaderSignatureException|SignatureException|AlreadySetException|SignatureExtractionException $e) {
    displayLogger('Prepare Verification Error '.$e->getMessage());

    return;
}
displayLogger('prepareVerification from Alice Request at Bob\'s Server without Errors');
/*
 * with the keyId you should be able to download the public pem
 */
$keyId = $verifierConfiguration->metadata->keyid;

if (null !== $keyId) {
    displayLogger('Alice sends the KeyId in her request: '.$keyId);
    displayLogger('Bob tries to request the public pem from Alice via the KeyId-Url.');
    /*
     * Requesting the Public Pem from Alice. This Cert is need to check the Requested Signature
     */
    $psr17Factory  = new Psr17Factory();
    $psrPemRequest = new PsrHttpFactory(
        serverRequestFactory: $psr17Factory,
        streamFactory       : $psr17Factory,
        uploadedFileFactory : $psr17Factory,
        responseFactory     : $psr17Factory,
    )->createRequest(symfonyRequest: Request::create($keyId));

    try {
        $bobPublicPem = new Client()->send($psrPemRequest)
                                    ->getBody()
        ;
    } catch (GuzzleException $e) {
        displayLogger('Getting Certificate from Bob Error: '.$e->getMessage());

        return;
    }

    displayLogger('Bob has requested and received the Public Pem from Alice.');
    displayLogger('..with content of pem: ');
    displayLogger((string)$bobPublicPem, 1, false);

    $verifierConfiguration->setPublicPem(publicPem: (string)$bobPublicPem);

    /*
     * When using mastodon it does not request in the metadata the signature algorithm
     * The only supported algorithm is still RSASSA-PKCS1-v1_5 Using SHA-256
     */
    /*
     * $verifierConfiguration->setSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_256);
     */
    /*
     * $verifierConfiguration->setContentDigestAlgo(DigestAlgorithmsEnum::sha256);
     */
    displayLogger('Bob now verifies the incoming signed request from Alice with her public pem.');
    /**
     * Verify the generated request from above(receiver-side)
     */
    $verifier = new VerifyRequest($verifyPrepareInstance);
    try {
        $res = $verifier->verifying();
    } catch (AbstractRfc9421Exception  $e) {
        displayLogger('Verify the Signed Request Error: '.$e->getMessage());

        return;
    }
    displayLogger('During the verify process there was no exception thrown.');

    displayLogger('The Verify process has endet with the result: '.($res ? 'true' : 'false'));

    /*
     * After successfully verifying you have access to verified header
     */

    if ($res) {
        $signedHeaderLines = $verifier->signedHeaderLines;

        /*
         * Also the $metadata are verified
         */
        $metadata = $verifier->getSigningConfiguration()->metadata;

        /*
         * or something
         */
        if (null !== $metadata->created && ($currentDateTime + 6000) > $metadata->created) {
            // ...

            displayLogger('created: '.$metadata->created);
        }

        if ($verifier->hasValidatedBody) {
            displayLogger('The body of the Request from bob was signed and is valid.');
            displayLogger('Content of the body:');

            $body = (string)$verifier->getMessage()
                                     ->getBody()
            ;
            displayLogger($body, 1, false);
        } else {
            displayLogger('The body was not signed by Alice.');
        }
    }
}
