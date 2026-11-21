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

use Filefabrik\Rfc9421\Enums\DerivedComponentEnum;
use Filefabrik\Rfc9421\Enums\SigningEnum;
use Filefabrik\Rfc9421\Exceptions\PrepareMethodsException;
use Filefabrik\Rfc9421\Exceptions\UnexpectedValue;
use Filefabrik\Rfc9421\Extractors\DecodeSignatureComponentEntry;
use Filefabrik\Rfc9421\ResponseSigner;
use Filefabrik\Rfc9421\Signer\Signatures\ReadLines;
use Filefabrik\Rfc9421\SigningComponents;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421Test\Prepared\SignedAndVerifiedRequest;
use Filefabrik\Rfc9421Test\Tools\CertHelper;
use GuzzleHttp\Psr7\Response;
use JsonException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * todo signing with ;asReq
 */
#[CoversClass(ResponseSigner::class)]
final class ResponseSignerTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        require_once dirname(__DIR__, 2).'/src/register-openssl-signing-verify.php';
    }

    /**
     * todo missing tests
     * todo with ;asReq
     * todo empty @query-params
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EmptyException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     * @throws \Filefabrik\Rfc9421\Exceptions\ProcessingException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testSign(): void
    {
        $certInstance = new CertHelper('rsa');

        $response = new Response(headers: [], body: '{"recipient":"Alex","new-amount":900000000}');

        //... now sign the response
        $signatureComponents          = new SigningComponents()
            // singing the status as relevant information for the requester Alice, to ensure the status
            ->sign(DerivedComponentEnum::status)
            // content-length is covered by a $this->addSigningMethod()
            ->sign('content-length')
        ;
        $responseSigningConfiguration = new SigningConfiguration()
            ->setSigningComponents($signatureComponents)
            // make sure the receiver Alice expects the following response Key
            ->setSignatureKeyName('responseSig=')
            // the sender of the request expects an algo that Bob can check while receive this signed response
            ->setSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_512)
            // set alice private pem to encrypt the response
            ->setPrivatePem($certInstance->getPrivatPemContent())
            // singing the response body

            ->withContentDigest('sha256')
        ;

        $responseSigner = new ResponseSigner(message: $response, signingConfiguration: $responseSigningConfiguration);

        $signedResponse = $responseSigner->sign();
        $signedHeaders  = $signedResponse->getHeaders();
        self::assertSame(
            'responseSig=("@status" "content-length" "content-digest");alg="rsa-v1-5-sha512"',
            $signedHeaders['signature-input'][0],
        );
        $headerKeys = array_keys($signedHeaders);
        self::assertContains('content-digest', $headerKeys);

        self::assertSame(
            'responseSig=("@status" "content-length" "content-digest");alg="rsa-v1-5-sha512"',
            $responseSigner->signatureBase,
        );

        $signedLinesText = <<<text
            "@status": 200
            "content-length": 43
            "content-digest": sha-256=:g0bQfdyBGX+NWVB/KAlGzZGIcOPzZ1rcMg/BFrGSPtI=:
            "@signature-params": responseSig=("@status" "content-length" "content-digest");alg="rsa-v1-5-sha512"
            text;

        self::assertSame($signedLinesText, $responseSigner->signingParametersString);
    }

    /**
     * todo missing tests
     * todo with asReq
     * todo empty @query-params
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EmptyException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\ProcessingException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testSignCanNotSignQuery(): void
    {
        $this->expectException(PrepareMethodsException::class);
        $this->expectExceptionMessageIsOrContains('Error during calling the method with key: "@query":');

        $certInstance       = new CertHelper('rsa');
        $responseBodyParams = ['account' => 'Alex', 'new-amount' => 900000000];
        try {
            $responseBodyJson = json_encode($responseBodyParams, JSON_THROW_ON_ERROR);
        } catch (JsonException $jsonException) {
            echo $jsonException->getMessage();

            return;
        }

        $response = new Response(headers: [], body: $responseBodyJson);

        //... now sign the response
        $signingComponents            = new SigningComponents()
            // singing the status as relevant information for the requester Alice, to ensure the status
            ->signHeader(DerivedComponentEnum::status)
            // content-length is covered by a $this->addSigningMethod()
            ->signHeader('content-length')
            ->signHeader(DerivedComponentEnum::query)
        ;
        $responseSigningConfiguration = new SigningConfiguration(signingComponents: $signingComponents)

            // make sure the receiver Alice expects the following response Key
            ->setSignatureKeyName('responseSig=')

            // the sender of the request expects an algo that Bob can check while receive this signed response
            ->setSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_512)
            // set alice private pem to encrypt the response
            ->setPrivatePem($certInstance->getPrivatPemContent())
            // singing the response body
            ->withContentDigest('sha256')
        ;

        $responseSigner = new ResponseSigner(message: $response, signingConfiguration: $responseSigningConfiguration);

        $responseSigner->sign();
    }

    /**
     * todo missing tests
     * todo with ;asReq
     * todo empty @query-params
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EmptyException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     * @throws \Filefabrik\Rfc9421\Exceptions\ProcessingException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testSignWithReq(): void
    {
        $certInstance = new CertHelper('rsa');

        $response = new Response(headers: [], body: '{"recipient":"Alex","new-amount":900000000}');

        //... now sign the response

        $signingComponents = new SigningComponents()
            // test asReq
            ->sign('"content-digest"', true)
            //->signHeader('"@method";asReq')
            // singing the status as relevant information for the requester Alice, to ensure the status
            ->sign(DerivedComponentEnum::status)
            // content-length is covered by a $this->addSigningMethod()
            ->sign('content-length')
        ;

        $responseSigningConfiguration = new SigningConfiguration(signingComponents: $signingComponents)
            // make sure the receiver Alice expects the following response Key
            ->setSignatureKeyName('responseSig=')
            // the sender of the request expects an algo that Bob can check while receive this signed response
            ->setSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_512)
            // set alice private pem to encrypt the response
            ->setPrivatePem($certInstance->getPrivatPemContent())
            // singing the response body
            //  ->setContentDigestAlgo('sha256')
            ->withContentDigest('sha256')
        ;

        // simulate a request

        $expectedLines = new ReadLines([
            DecodeSignatureComponentEntry::toLine(
                '"content-digest": sha-512=:ky7+tHa2jKBwuLnRdgxC418SwYUSuSk4NXiGsAYA6aKby9eVl2PTRsVjwEn1EjPQ93BxKbqzkCh58NqE1nL67A==:',
            ),
        ]);

        $responseSigner = new ResponseSigner(
            message             : $response,
            signingConfiguration: $responseSigningConfiguration,
            expectedSignedLines : $expectedLines,
        );

        $signedResponse = $responseSigner->sign();
        $signedHeaders  = $signedResponse->getHeaders();
        self::assertSame(
            'responseSig=("content-digest";req "@status" "content-length" "content-digest");alg="rsa-v1-5-sha512"',
            $signedHeaders['signature-input'][0],
        );
        $headerKeys = array_keys($signedHeaders);
        self::assertContains('content-digest', $headerKeys);

        self::assertSame(
            'responseSig=("content-digest";req "@status" "content-length" "content-digest");alg="rsa-v1-5-sha512"',
            $responseSigner->signatureBase,
        );

        $signedLinesText = <<<text
            "content-digest";req: sha-512=:ky7+tHa2jKBwuLnRdgxC418SwYUSuSk4NXiGsAYA6aKby9eVl2PTRsVjwEn1EjPQ93BxKbqzkCh58NqE1nL67A==:
            "@status": 200
            "content-length": 43
            "content-digest": sha-256=:g0bQfdyBGX+NWVB/KAlGzZGIcOPzZ1rcMg/BFrGSPtI=:
            "@signature-params": responseSig=("content-digest";req "@status" "content-length" "content-digest");alg="rsa-v1-5-sha512"
            text;

        self::assertSame($signedLinesText, $responseSigner->signingParametersString);
    }

    /**
     * todo missing tests
     * todo with ;asReq
     * todo empty @query-params
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EmptyException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\ProcessingException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     * @throws JsonException
     */
    public function testSignWithReqFromSignedRequest(): void
    {
        $verifier = SignedAndVerifiedRequest::get();

        $certInstance = new CertHelper('rsa');

        $response = new Response(headers: [], body: '{"recipient":"Alex","new-amount":900000000}');

        //... now sign the response
        $signedComponents             = new SigningComponents()
            // test asReq
            ->sign('"content-digest"', true)
            ->sign('"@authority"', true)
            //->signHeader('"@method";asReq')
            // singing the status as relevant information for the requester Alice, to ensure the status
            ->sign(DerivedComponentEnum::status)
            // content-length is covered by a $this->addSigningMethod()
            ->sign('content-length')
        ;
        $responseSigningConfiguration = new SigningConfiguration(signingComponents: $signedComponents)

            // make sure the receiver Alice expects the following response Key
            ->setSignatureKeyName('responseSig=')

            // the sender of the request expects an algo that Bob can check while receive this signed response
            ->setSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_512)
            // set alice private pem to encrypt the response
            ->setPrivatePem($certInstance->getPrivatPemContent())
            // singing the response body
            ->withContentDigest('sha256')
        ;

        $responseSigner = new ResponseSigner(
            message             : $response,
            signingConfiguration: $responseSigningConfiguration,
            expectedSignedLines : $verifier->getSignedHeaderLines(),
        );

        $signedResponse = $responseSigner->sign();
        $signedHeaders  = $signedResponse->getHeaders();
        self::assertSame(
            'responseSig=("content-digest";req "@authority";req "@status" "content-length" "content-digest");alg="rsa-v1-5-sha512"',
            $signedHeaders['signature-input'][0],
        );
        $headerKeys = array_keys($signedHeaders);
        self::assertContains('content-digest', $headerKeys);

        self::assertSame(
            'responseSig=("content-digest";req "@authority";req "@status" "content-length" "content-digest");alg="rsa-v1-5-sha512"',
            $responseSigner->signatureBase,
        );

        $signedLinesText = <<<text
            "content-digest";req: sha-512=:ky7+tHa2jKBwuLnRdgxC418SwYUSuSk4NXiGsAYA6aKby9eVl2PTRsVjwEn1EjPQ93BxKbqzkCh58NqE1nL67A==:
            "@authority";req: target-host.com
            "@status": 200
            "content-length": 43
            "content-digest": sha-256=:g0bQfdyBGX+NWVB/KAlGzZGIcOPzZ1rcMg/BFrGSPtI=:
            "@signature-params": responseSig=("content-digest";req "@authority";req "@status" "content-length" "content-digest");alg="rsa-v1-5-sha512"
            text;

        self::assertSame($signedLinesText, $responseSigner->signingParametersString);
    }

    /**
     * todo missing tests
     * todo with ;asReq
     * todo empty @query-params
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EmptyException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\ProcessingException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     * @throws JsonException
     */
    public function testSignWithReqWithoutSignedRequest(): void
    {
        $this->expectExceptionObject(
            new UnexpectedValue('For Response signing the signed Request must be set into the Signer!'),
        );

        $certInstance = new CertHelper('rsa');

        $response = new Response(headers: [], body: '{"recipient":"Alex","new-amount":900000000}');

        //... now sign the response

        // test asReq
        $signedComponents = new SigningComponents()->signHeader('"content-digest";req')
                                                   ->signHeader('"@authority";req')
            //->signHeader('"@method";asReq')
            // singing the status as relevant information for the requester Alice, to ensure the status
                                                   ->signHeader(DerivedComponentEnum::status)
            // content-length is covered by a $this->addSigningMethod()
                                                   ->signHeader('content-length')
        ;

        $responseSigningConfiguration = new SigningConfiguration()->setSigningComponents($signedComponents)

            // make sure the receiver Alice expects the following response Key
                                                                  ->setSignatureKeyName('responseSig=')


            // the sender of the request expects an algo that Bob can check while receive this signed response
                                                                  ->setSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_512)
            // set alice private pem to encrypt the response
                                                                  ->setPrivatePem($certInstance->getPrivatPemContent())
            // singing the response body
                                                                  ->withContentDigest('sha256')
        ;

        $responseSigner = new ResponseSigner(
            message             : $response,
            signingConfiguration: $responseSigningConfiguration,
        );

        $responseSigner->sign();
    }

}
