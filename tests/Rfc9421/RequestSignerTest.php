<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test;

use DateTime;
use Filefabrik\Rfc9421\Enums\SigningEnum;
use Filefabrik\Rfc9421\RequestSigner;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421Test\Tools\CertHelper;
use GuzzleHttp\Psr7\Request;
use JsonException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
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
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     * @throws JsonException
     */
    public function testSign(): void
    {
        $targetUrl            = 'http://localhost:8001/mastodon?param=value&foo=bar&baz=bat%2Dman';
        $certInstance         = new CertHelper('rsa');
        $currentDateTime      = new DateTime()->getTimestamp();
        $expireDateTime       = $currentDateTime + 7200;
        $signingConfiguration = new SigningConfiguration()
            /*
             * todo documentation for addSigningMethod for content-type
             */

            ->addSigningMethod(
                componentName: 'content-type',
                closure      : fn (
                    RequestInterface $request,
                ): string => $request->getHeaderLine('content-type'),
            )
            ->signHeader('content-type')
            ->signHeader('"@query-param";name="baz"')
            ->signHeader('@method')
            ->signHeader('x-Origin-host')
            ->signHeader('"example-dict";sf')
            ->signHeader('"example-dict";key="d"')
            ->signHeader('"example-header";bs')
            ->setSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_512)
            ->setCreated($currentDateTime)
            ->setExpires($expireDateTime)
            ->setTag('mastodon')
            ->setKeyid('http://127.0.0.1:8001/actor#main-key')
            ->withNonce('nonce-now')
            ->setContentDigestAlgo('sha512')
            // content digest signer key
            ->setSignatureKeyName('testValue=')
            // for signing needs private
            ->setPrivatePem($certInstance->getPrivatPemContent())
        ;

        $signingConfiguration->getStructuredFields()
                             ->add('example-dict', 'dictionary')
        ;
        /**
         * Prepare the Request
         */
        $request = new Request(
            method : 'POST',
            uri    : $targetUrl,
            headers: [
                'x-origin-host' => 'mytest.com',
                'Example-Dict'  => '  a=1,    b=2;x=1;y=2,   c=(a   b   c), d ',
                'content-type'  => 'application/json',
            ],
            body   : '{"recipient":"Alex","amount":900000000,"text":"fdawsfs"}',
        );

        $request       = $request->withAddedHeader('example-header', 'value, with, lots')
                                 ->withAddedHeader('example-header', 'of, commas')
        ;
        $signer        = new RequestSigner(message: $request, signingConfiguration: $signingConfiguration);
        $signedRequest = $signer->sign();

        self::assertTrue($signedRequest->hasHeader('content-digest'));

        $signingLinesValues = $signer->getSignedLines()
                                     ->values()
        ;
        $signingLinesKeys   = $signer->getSignedLines()
                                     ->keys()
        ;

        self::assertContains('"@query-param";name="baz"', $signingLinesKeys);
        // todo _baz_ with underscores is wrong it should be as @query-param
        self::assertContains('"@query-param";name="baz": bat-man', $signingLinesValues);

        self::assertContains('"@method"', $signingLinesKeys);
        self::assertContains('"@method": POST', $signingLinesValues);

        self::assertContains('"x-origin-host"', $signingLinesKeys);
        self::assertContains('"x-origin-host": mytest.com', $signingLinesValues);

        self::assertContains('"example-dict";sf', $signingLinesKeys);
        self::assertContains('"example-dict";sf: a=1, b=2;x=1;y=2, c=(a b c), d', $signingLinesValues);

        self::assertContains('"example-dict";key="d"', $signingLinesKeys);
        self::assertContains('"example-dict";key="d": ?1', $signingLinesValues);

        self::assertContains('"content-digest"', $signingLinesKeys);
        self::assertContains(
            '"content-digest": sha-512=:ky7+tHa2jKBwuLnRdgxC418SwYUSuSk4NXiGsAYA6aKby9eVl2PTRsVjwEn1EjPQ93BxKbqzkCh58NqE1nL67A==:',
            $signingLinesValues,
        );
        /*
         * BS Test with two fields
         */
        self::assertContains(
            '"example-header";bs: :dmFsdWUsIHdpdGgsIGxvdHM=:, :b2YsIGNvbW1hcw==:',
            $signingLinesValues,
        );
        self::assertContains('"content-type": application/json', $signingLinesValues);
        $signatureLines = <<<TEXT
            "content-type": application/json
            "@query-param";name="baz": bat-man
            "@method": POST
            "x-origin-host": mytest.com
            "example-dict";sf: a=1, b=2;x=1;y=2, c=(a b c), d
            "example-dict";key="d": ?1
            "example-header";bs: :dmFsdWUsIHdpdGgsIGxvdHM=:, :b2YsIGNvbW1hcw==:
            "content-digest": sha-512=:ky7+tHa2jKBwuLnRdgxC418SwYUSuSk4NXiGsAYA6aKby9eVl2PTRsVjwEn1EjPQ93BxKbqzkCh58NqE1nL67A==:
            "@signature-params": testValue=("content-type" "@query-param";name="baz" "@method" "x-origin-host" "example-dict";sf "example-dict";key="d" "example-header";bs "content-digest");created=$currentDateTime;expires=$expireDateTime;nonce="nonce-now";keyid="http://127.0.0.1:8001/actor#main-key";alg="rsa-v1-5-sha512";tag="mastodon"
            TEXT;
        self::assertSame($signatureLines, $signer->signingParametersString);
    }

    /**
     * Just a running Test
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     * @throws JsonException
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
        $request = new Request(
            method : 'POST',
            uri    : $targetUrl,
            headers: ['x-origin-host' => 'mytest.com'],
            body   : '{"recipient":"Alex","amount":900000000,"text":"fdawsfs"}',
        );

        $signer        = new RequestSigner(message: $request, signingConfiguration: $signingConfiguration);
        $signedRequest = $signer->sign();
        self::assertInstanceOf(Request::class, $signedRequest);
        self::assertFalse($signedRequest->hasHeader('content-digest'));
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
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
        $request = new Request(
            method : 'POST',
            uri    : $targetUrl,
            headers: ['x-origin-host' => 'mytest.com'],
            body   : '{"recipient":"Alex","amount":900000000,"text":"fdawsfs"}',
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
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
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
        $request = new Request(
            method : 'POST',
            uri    : $targetUrl,
            headers: ['x-origin-host' => 'mytest.com'],
            body   : '{"recipient":"Alex","amount":900000000,"text":"fdawsfs"}',
        );

        $signer = new RequestSigner(message: $request, signingConfiguration: $signingConfiguration);
        $signer->sign();

        self::assertCount(
            1,
            $signer->getSignedLines()
                   ->getLines(),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
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
        $request = new Request(
            method : 'POST',
            uri    : $targetUrl,
            headers: ['x-origin-host' => 'mytest.com'],
            body   : '{"recipient":"Alex","amount":900000000,"text":"fdawsfs"}',
        );

        $signer = new RequestSigner(message: $request, signingConfiguration: $signingConfiguration);
        $signer->getSignedLines()
               ->toKeyValueArray()
        ;
    }
}
