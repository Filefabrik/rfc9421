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

use Filefabrik\Rfc9421\Enums\SigningEnum;
use Filefabrik\Rfc9421\ResponseSigner;
use Filefabrik\Rfc9421\Signer\Signatures\ReadLines;
use Filefabrik\Rfc9421\Signer\Signatures\WriteLines;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421\VerifyResponsePrepare;
use Filefabrik\Rfc9421Test\Tools\CertHelper;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

#[CoversClass(VerifyResponsePrepare::class)]
final class VerifyResponsePrepareTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        require_once dirname(__DIR__, 2).'/src/register-openssl-signing-verify.php';
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\ProcessingException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     * @throws \Filefabrik\Rfc9421\Exceptions\EmptyException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public function testSetExpectedReqSigners(): void
    {
        $expectSignedReqLine = new ReadLines(
            new WriteLines()->addEntry(
                '"content-digest"',
                '"content-digest": sha-256=:ky7+tHa2jKBwuLnRdgxC418SwYUSuSk4NXiGsAYA6aKby9eVl2PTRsVjwEn1EjPQ93BxKbqzkCh58NqE1nL67A==:',
            ),
        );

        $signedResponse = $this->getSignedResponse();

        $verifyResponseConfiguration = new SigningConfiguration();
        $prepare                     = new VerifyResponsePrepare(
            message             : $signedResponse,
            signingConfiguration: $verifyResponseConfiguration,
        )->setExpectedReqSigners($expectSignedReqLine);
        $prepared                    = $prepare->prepareVerification();

        self::assertTrue($prepared);

        self::assertSame($prepare->getExpectedSigners(), $expectSignedReqLine);
    }

    /**
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
    private function getSignedResponse(): ResponseInterface
    {
        $rsaCert = new CertHelper('rsa');

        $response                    = new Response();
        $responseSignerConfiguration = new SigningConfiguration();
        $responseSignerConfiguration->setSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_512)
                                    ->signHeader('@status')
                                    ->setPrivatePem($rsaCert->getPrivatPemContent())
                                    ->setSignatureKeyName('todo=')
        ;
        $responseSigner = new ResponseSigner(
            message             : $response,
            signingConfiguration: $responseSignerConfiguration,
        );

        return $responseSigner->sign();
    }

    /**
     * Basic Flow of preparation
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
     */
    public function testSuccess(): void
    {
        $signedResponse = $this->getSignedResponse();

        $verifyResponseConfiguration = new SigningConfiguration();
        $prepare                     = new VerifyResponsePrepare(
            message             : $signedResponse,
            signingConfiguration: $verifyResponseConfiguration,
        );
        $prepared                    = $prepare->prepareVerification();

        self::assertTrue($prepared);

        $sInstance = $prepare->getSignatureInputInstance();
        $c         = $sInstance->signatureInput;

        // ensure the values are set
        self::assertSame('"@status"', $c->components);
        self::assertSame('alg="rsa-v1-5-sha512"', $c->metadata);
        self::assertSame('todo=', $sInstance->signatureNameKey);
        self::assertSame('todo=("@status");alg="rsa-v1-5-sha512"', $sInstance->headerSignatureInputValue);
    }

    /**
     * Basic Flow of preparation
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
     */
    public function testSuccessREQ(): void
    {
        $signedResponse = $this->getSignedResponseREQ();

        $verifyResponseConfiguration = new SigningConfiguration();
        $prepare                     = new VerifyResponsePrepare(
            message             : $signedResponse,
            signingConfiguration: $verifyResponseConfiguration,
        );
        $prepared                    = $prepare->prepareVerification();

        self::assertTrue($prepared);

        $sInstance = $prepare->getSignatureInputInstance();
        $c         = $sInstance->signatureInput;

        // ensure the values are set
        self::assertSame('"@status" "content-digest";req', $c->components);
        self::assertSame('alg="rsa-v1-5-sha512"', $c->metadata);
        self::assertSame('todo=', $sInstance->signatureNameKey);
        self::assertSame(
            'todo=("@status" "content-digest";req);alg="rsa-v1-5-sha512"',
            $sInstance->headerSignatureInputValue,
        );
    }

    /**
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
    private function getSignedResponseREQ(): ResponseInterface
    {
        $rsaCert = new CertHelper('rsa');

        $requestSigned               = new ReadLines(
            new WriteLines()->addEntry(
                '"content-digest"',
                '"content-digest": sha-512=:ky7+tHa2jKBwuLnRdgxC418SwYUSuSk4NXiGsAYA6aKby9eVl2PTRsVjwEn1EjPQ93BxKbqzkCh58NqE1nL67A==:',
            ),
        );
        $response                    = new Response();
        $responseSignerConfiguration = new SigningConfiguration();
        $responseSignerConfiguration->setSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_512)
                                    ->signHeader('@status')
                                    ->signHeader('"content-digest";req')
                                    ->setPrivatePem($rsaCert->getPrivatPemContent())
                                    ->setSignatureKeyName('todo=')
        ;
        $responseSigner = new ResponseSigner(
            message             : $response,
            signingConfiguration: $responseSignerConfiguration,
            expectedSignedLines : $requestSigned,
        );

        return $responseSigner->sign();
    }
}
