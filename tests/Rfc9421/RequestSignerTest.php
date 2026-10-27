<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test;

use DateTime;
use Filefabrik\Rfc9421\Cryptography\SigningEnum;
use Filefabrik\Rfc9421\RequestSigner;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421Test\Tools\CertHelper;
use GuzzleHttp\Psr7\Request;
use JsonException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

#[CoversClass(RequestSigner::class)]
final class RequestSignerTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        require_once dirname(__DIR__, 2).'/src/register-openssl-signing-verify.php';
    }

    /**
     * Just a running Test
     *
     * @throws \Bakame\Http\StructuredFields\StructuredFieldError
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     * @throws JsonException
     */
    public function testSign(): void
    {
        $targetUrl       = 'http://localhost:8001/mastodon?param=value&foo=bar&baz=bat%2Dman';
        $certInstance    = new CertHelper('rsa');
        $currentDateTime = new DateTime()->getTimestamp();

        $signingConfiguration = new SigningConfiguration()->signHeader('"@query-param";name="baz"')
                                                          ->signHeader('@method')
                                                          ->signHeader('x-Origin-host')
                                                          ->signHeader('"example-dict";sf')
                                                          ->setSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_512)
                                                          ->setCreated($currentDateTime)
                                                          ->setExpires($currentDateTime + 7200)
                                                          ->setTag('mastodon')
                                                          ->setKeyid('http://127.0.0.1:8001/actor#main-key')
                                                          ->withNonce('nonce-now')
                                                          ->setContentDigestAlgo('sha512')
            // content digest signer key
                                                          ->setSignatureKeyName('testValue=')
            // for signing needs private
                                                          ->setPrivatePem($certInstance->getPrivatPemContent())
        ;

        $signingConfiguration->structuredFields->add('example-dict', 'dictionary');
        /**
         * Prepare the Request
         */
        $bodyParams = ['recipient' => 'Alex', 'amount' => 900000000, 'text' => 'fdawsfs'];
        $bodyJson   = json_encode($bodyParams, JSON_THROW_ON_ERROR);
        $request    = new Request(
            method : 'POST',
            uri    : $targetUrl,
            headers: ['x-origin-host' => 'mytest.com', 'Example-Dict' => '  a=1,    b=2;x=1;y=2,   c=(a   b   c), d '],
            body   : $bodyJson,
        );

        $signer        = new RequestSigner(message: $request, signingConfiguration: $signingConfiguration);
        $signedRequest = $signer->sign();
        self::assertInstanceOf(Request::class, $signedRequest);
        self::assertTrue($signedRequest->hasHeader('content-digest'));
    }

    /**
     * Just a running Test
     *
     * @throws \Bakame\Http\StructuredFields\StructuredFieldError
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     * @throws JsonException|\Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testSignWithoutContentDigest(): void
    {
        $targetUrl       = 'http://localhost:8001/mastodon?param=value&foo=bar&baz=bat%2Dman';
        $certInstance    = new CertHelper('rsa');
        $currentDateTime = new DateTime()->getTimestamp();

        $signingConfiguration = new SigningConfiguration()
            ->signHeader('@method')
            ->signHeader('x-Origin-host')
            ->setSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_512)
            ->setCreated($currentDateTime)
            ->setExpires($currentDateTime + 7200)
            ->setTag('mastodon')
            ->setKeyid('http://127.0.0.1:8001/actor#main-key')
            ->withNonce('nonce-now')
            //->setContentDigestAlgo('sha512')
            // content digest signer key
            ->setSignatureKeyName('testValue=')
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
            uri    : $targetUrl,
            headers: ['x-origin-host' => 'mytest.com'],
            body   : $bodyJson,
        );

        $signer        = new RequestSigner(message: $request, signingConfiguration: $signingConfiguration);
        $signedRequest = $signer->sign();
        self::assertInstanceOf(Request::class, $signedRequest);
        self::assertFalse($signedRequest->hasHeader('content-digest'));
    }

    /**
     * @throws \Bakame\Http\StructuredFields\StructuredFieldError
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     * @throws JsonException|\Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testGetSignedLines(): void
    {
        $targetUrl       = 'http://localhost:8001/mastodon?param=value&foo=bar&baz=bat%2Dman';
        $certInstance    = new CertHelper('rsa');
        $currentDateTime = new DateTime()->getTimestamp();

        $signingConfiguration = new SigningConfiguration()
            ->signHeader('@method')
            ->signHeader('x-Origin-host')
            ->setSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_512)
            ->setCreated($currentDateTime)
            ->setExpires($currentDateTime + 7200)
            ->setTag('mastodon')
            ->setKeyid('http://127.0.0.1:8001/actor#main-key')
            ->withNonce('nonce-now')
            ->setContentDigestAlgo('sha512')
            // content digest signer key
            ->setSignatureKeyName('testValue=')
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
            uri    : $targetUrl,
            headers: ['x-origin-host' => 'mytest.com'],
            body   : $bodyJson,
        );

        $signer        = new RequestSigner(message: $request, signingConfiguration: $signingConfiguration);
        $signedRequest = $signer->sign();
        self::assertInstanceOf(Request::class, $signedRequest);
        self::assertTrue($signedRequest->hasHeader('content-digest'));
        self::assertCount(
            4,
            $signer->getSignedLines()
                   ->toKeyValueArray(),
        );
    }

    /**
     * @throws \Bakame\Http\StructuredFields\StructuredFieldError
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     * @throws JsonException
     */
    public function testGetSignedLinesOne(): void
    {
        $targetUrl       = 'http://localhost:8001/mastodon?param=value&foo=bar&baz=bat%2Dman';
        $certInstance    = new CertHelper('rsa');
        $currentDateTime = new DateTime()->getTimestamp();

        $signingConfiguration = new SigningConfiguration()
            ->setSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_512)
            ->setCreated($currentDateTime)
            ->setExpires($currentDateTime + 7200)
            ->setTag('mastodon')
            ->setKeyid('http://127.0.0.1:8001/actor#main-key')
            ->withNonce('nonce-now')

            // content digest signer key
            ->setSignatureKeyName('testValue=')
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
            uri    : $targetUrl,
            headers: ['x-origin-host' => 'mytest.com'],
            body   : $bodyJson,
        );

        $signer        = new RequestSigner(message: $request, signingConfiguration: $signingConfiguration);
        $signedRequest = $signer->sign();
        self::assertInstanceOf(Request::class, $signedRequest);

        self::assertCount(1, $signer->getSignedLines()->lines);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws JsonException
     */
    public function testGetSignedLinesThrow(): void
    {
        $this->expectExceptionObject(
            new UnexpectedValueException(
                'Signing\Lines not set',
            ),
        );

        $targetUrl       = 'http://localhost:8001/mastodon?param=value&foo=bar&baz=bat%2Dman';
        $certInstance    = new CertHelper('rsa');
        $currentDateTime = new DateTime()->getTimestamp();

        $signingConfiguration = new SigningConfiguration()
            ->setSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_512)
            ->setCreated($currentDateTime)
            ->setExpires($currentDateTime + 7200)
            ->setTag('mastodon')
            ->setKeyid('http://127.0.0.1:8001/actor#main-key')
            ->withNonce('nonce-now')

            // content digest signer key
            ->setSignatureKeyName('testValue=')
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
            uri    : $targetUrl,
            headers: ['x-origin-host' => 'mytest.com'],
            body   : $bodyJson,
        );

        $signer = new RequestSigner(message: $request, signingConfiguration: $signingConfiguration);
        $signer->getSignedLines()
               ->toKeyValueArray()
        ;
    }
}
