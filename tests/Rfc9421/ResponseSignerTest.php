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
use Filefabrik\Rfc9421\Enums\DerivedComponentEnum;
use Filefabrik\Rfc9421\Enums\DigestAlgorithmsEnum;
use Filefabrik\Rfc9421\Enums\SigningEnum;
use Filefabrik\Rfc9421\Exceptions\PrepareMethodsException;
use Filefabrik\Rfc9421\Exceptions\UnexpectedValue;
use Filefabrik\Rfc9421\Extractors\DecodeSignatureComponentEntry;
use Filefabrik\Rfc9421\RequestSigner;
use Filefabrik\Rfc9421\ResponseSigner;
use Filefabrik\Rfc9421\Signer\Signatures\ReadLines;
use Filefabrik\Rfc9421\Signer\Signatures\WriteLines;
use Filefabrik\Rfc9421\SigningComponents;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421\VerifyRequest;
use Filefabrik\Rfc9421\VerifyRequestPrepare;
use Filefabrik\Rfc9421Test\Tools\CertHelper;
use GuzzleHttp\Psr7\Request;
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
            ->sign('content-digest')
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
            ->setContentDigestAlgo('sha256')
            ->mustCreateContentDigest()
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
            ->setContentDigestAlgo('sha256')
        ;

        $responseSigner = new ResponseSigner(message: $response, signingConfiguration: $responseSigningConfiguration);

        $signedResponse = $responseSigner->sign();

        $signedHeaders = $signedResponse->getHeaders();
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
            "content-length": 41
            "content-digest": sha-256=:ZCEnsqX7hCc+COMsU1B7wNyKEdBqHR0ItAHl8HsEbus=:
            "@signature-params": responseSig=("@status" "content-length" "content-digest");alg="rsa-v1-5-sha512"
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
            ->sign('content-digest')
        ;

        $responseSigningConfiguration = new SigningConfiguration(signingComponents: $signingComponents)
            // make sure the receiver Alice expects the following response Key
            ->setSignatureKeyName('responseSig=')
            // the sender of the request expects an algo that Bob can check while receive this signed response
            ->setSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_512)
            // set alice private pem to encrypt the response
            ->setPrivatePem($certInstance->getPrivatPemContent())
            // singing the response body
            ->setContentDigestAlgo('sha256')
            ->mustCreateContentDigest()
        ;

        // simulate a request
        $writeLines = new WriteLines();

        $writeLines->add(
            DecodeSignatureComponentEntry::toLine(
                '"content-digest": sha-512=:ky7+tHa2jKBwuLnRdgxC418SwYUSuSk4NXiGsAYA6aKby9eVl2PTRsVjwEn1EjPQ93BxKbqzkCh58NqE1nL67A==:',
            ),
        );
        $rl = new ReadLines($writeLines);

        $responseSigner = new ResponseSigner(
            message             : $response,
            signingConfiguration: $responseSigningConfiguration,
            expectedSignedLines : $rl,
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
        $verifier = $this->getSignedAndVerifiedRequest();

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
            ->sign('content-digest')
        ;
        $responseSigningConfiguration = new SigningConfiguration(signingComponents: $signedComponents)

            // make sure the receiver Alice expects the following response Key
            ->setSignatureKeyName('responseSig=')

            // the sender of the request expects an algo that Bob can check while receive this signed response
            ->setSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_512)
            // set alice private pem to encrypt the response
            ->setPrivatePem($certInstance->getPrivatPemContent())
            // singing the response body
            ->setContentDigestAlgo('sha256')
            ->mustCreateContentDigest()
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
     * Helper/Mock Method
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\ProcessingException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     * @throws JsonException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\EmptyException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     */
    private function getSignedAndVerifiedRequest(): VerifyRequest
    {
        $cert         = 'rsa';
        $signingAlgo  = SigningEnum::rsa_v_1_5_sha_512->name;
        $certInstance = new CertHelper($cert);

        $signatureKeyName = 'testing=';

        $signedComponents = new SigningComponents()->sign('@method')
                                                   ->sign('@authority')
                                                   ->sign('content-digest')
        ;
        /**
         * Prepare signingConfiguration
         */
        $currentDateTime = new DateTime()->getTimestamp();
        $configuration   = new SigningConfiguration();
        $configuration->setSigningComponents($signedComponents)
                      ->mustCreateContentDigest()
                      ->setContentDigestAlgo(contentDigestAlgo: DigestAlgorithmsEnum::sha512)
                      ->setSigningAlgorithm($signingAlgo)
                      ->setCreated($currentDateTime)
                      ->setExpires($currentDateTime + 7200)
                      ->setTag('mastodon')
                      ->withNonce('nonce-now')
                      ->setSignatureKeyName($signatureKeyName)
            // for signing needs private
                      ->setPrivatePem($certInstance->getPrivatPemContent())
        ;

        /**
         * Prepare the Request
         */
        $request = new Request(
            method : 'POST',
            uri    : 'https://target-host.com',
            headers: ['x-origin-host' => 'target-host.com'],
            body   : '{"recipient":"Alex","amount":900000000,"text":"fdawsfs"}',
        );

        $signer        = new RequestSigner($request, $configuration);
        $signedRequest = $signer->sign();

        /**
         * Now decrypt the Request
         */
        $verifyPrepare = new VerifyRequestPrepare($signedRequest, new SigningConfiguration());
        $verifyPrepare->prepareVerification();

        $verifier = new VerifyRequest($verifyPrepare);

        // optional before preparing verify and verifying
        $signerConfiguration = $verifier->getSigningConfiguration();
        //  ->setSignatureKeyName($signatureKeyName)
        // for verify needs public
        $signerConfiguration->setPublicPem($certInstance->getPublicPemContent());

        /*
         * now prepare the verify process
         */

        $verifier->verifying();

        return $verifier;
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
                                                   ->signHeader('content-digest')
        ;

        $responseSigningConfiguration = new SigningConfiguration()->setSigningComponents($signedComponents)

            // make sure the receiver Alice expects the following response Key
                                                                  ->setSignatureKeyName('responseSig=')


            // the sender of the request expects an algo that Bob can check while receive this signed response
                                                                  ->setSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_512)
            // set alice private pem to encrypt the response
                                                                  ->setPrivatePem($certInstance->getPrivatPemContent())
            // singing the response body
                                                                  ->setContentDigestAlgo('sha256')
                                                                  ->mustCreateContentDigest()
        ;

        $responseSigner = new ResponseSigner(
            message             : $response,
            signingConfiguration: $responseSigningConfiguration,
        );

        $responseSigner->sign();
    }
}
