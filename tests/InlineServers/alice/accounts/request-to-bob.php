<?php

declare(strict_types=1);

// Specify the URL and data
use Filefabrik\Rfc9421\Cryptography\SigningEnum;
use Filefabrik\Rfc9421\Exceptions\AlreadySetException;
use Filefabrik\Rfc9421\Exceptions\ContentDigestException;
use Filefabrik\Rfc9421\Exceptions\InvalidDerivedKeyException;
use Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException;
use Filefabrik\Rfc9421\Exceptions\SignatureException;
use Filefabrik\Rfc9421\RequestSigner;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421Test\Tools\CertHelper;
use GuzzleHttp\Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\RequestInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__.'/../../bootstrap.php';

require_once __DIR__.'/../../../../src/register-openssl-signing-verify.php';

$url = 'http://localhost:8001/accounts/from-alice?XDEBUG_SESSION=PHPSTORM';

$certInstance = new CertHelper('rsa', __DIR__.'/../Keys/');

displayLogger('Here is Alice\'s Server');
/*
 * Prepare signingConfiguration
 */
$currentDateTime = new DateTime()->getTimestamp();

try {
    $signingConfiguration = new SigningConfiguration()
        /*
         * content-type will be injected after signing so configure that we are signing manuell the content-type
         */
        ->processWithoutExistingHeader('content-type')
        /*
         * the method we are generating the content-type value (simply via header line)
         */
        ->withPrepareComponentsMethod(
            'content-type',
            fn (RequestInterface $request): string => $request->getHeaderLine('content-type'),
        )
        /*
         * tell the signing process that content-type has to be signed
         */
        ->signHeader('content-type')
        ->signHeader('@method')
        ->signHeader('x-Origin-host')
        ->signHeader('@authority')
        ->setSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_512)
        ->setCreated($currentDateTime)
        ->setExpires($currentDateTime + 7200)
        ->setTag('mastodon')
        ->setKeyid('http://localhost:8000/public-pem#main-key')
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
displayLogger('The Signing configuration is without errors.');
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
displayLogger('We are signing the @authority with $message->getHeaderLine(\'x-Origin-host\').');
displayLogger('// So Bob\'s Server must also set this method to getting the correct @authority value from header.');
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

$symfonyRequest = Request::create(
    uri    : $url,
    method : 'POST',
    content: $bodyJson,
);
$symfonyRequest->headers->add(['x-origin-host' => 'This-is-From-Bob.com', 'content-type' => 'application/json']);
// The HTTP_HOST server key must be set to avoid an unexpected error

$psr17Factory   = new Psr17Factory();
$psrHttpFactory = new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
$psrRequest     = $psrHttpFactory->createRequest($symfonyRequest);

$signer = new RequestSigner(message: $psrRequest, signingConfiguration: $signingConfiguration);
try {
    $signedRequest = $signer->sign();
} catch (AlreadySetException|SignatureException|InvalidHeaderKeyException|InvalidDerivedKeyException|ContentDigestException $e) {
    echo $e->getMessage();

    return;
}
displayLogger('During request signing at Alice Server there was no exception thrown.');
displayLogger('Now, send the Request to Bob', 2);
$bobsResponse = new Client()->send($signedRequest);
displayLogger('Response is back from Bob to Alice Server. Shows the Response-Body from Bob.', 2);

echo $bobsResponse->getBody();

displayLogger('', 1, false);
displayLogger('All Finished at Bob\'s Server');
