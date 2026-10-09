<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test;

use DateTime;
use Filefabrik\Rfc9421\Cryptography\SigningEnum;
use Filefabrik\Rfc9421\Signer;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421Test\Tools\CertHelper;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Signer::class)]
final class SignerTest extends TestCase
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
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidDerivedKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \JsonException
     */
    public function testSign(): void
    {
        $targetUrl       = 'http://localhost:8001/mastodon';
        $certInstance    = new CertHelper('rsa');
        $currentDateTime = new DateTime()->getTimestamp();

        $signingConfiguration = new SigningConfiguration()
            ->signHeader('@method')
            ->signHeader('x-Origin-host')
            ->withSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_512)
            ->withCreated($currentDateTime)
            ->withExpires($currentDateTime + 7200)
            ->withTag('mastodon')
            ->withKeyid('http://127.0.0.1:8001/actor#main-key')
            ->withNonce('nonce-now')
            ->setContentDigestHashAlgo('sha512')
            // content digest signer key
            ->withSignatureKeyName('testValue=')
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

        $signer        = new Signer(message: $request, signingConfiguration: $signingConfiguration);
        $signedRequest = $signer->sign();
        self::assertInstanceOf(Request::class, $signedRequest);
        self::assertTrue($signedRequest->hasHeader('content-digest'));
    }

    /**
     * Just a running Test
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidDerivedKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \JsonException
     */
    public function testSignWithoutContentDigest(): void
    {
        $targetUrl       = 'http://localhost:8001/mastodon';
        $certInstance    = new CertHelper('rsa');
        $currentDateTime = new DateTime()->getTimestamp();

        $signingConfiguration = new SigningConfiguration()
            ->signHeader('@method')
            ->signHeader('x-Origin-host')
            ->withSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_512)
            ->withCreated($currentDateTime)
            ->withExpires($currentDateTime + 7200)
            ->withTag('mastodon')
            ->withKeyid('http://127.0.0.1:8001/actor#main-key')
            ->withNonce('nonce-now')
            //->setContentDigestHashAlgo('sha512')
            // content digest signer key
            ->withSignatureKeyName('testValue=')
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

        $signer        = new Signer(message: $request, signingConfiguration: $signingConfiguration);
        $signedRequest = $signer->sign();
        self::assertInstanceOf(Request::class, $signedRequest);
        self::assertFalse($signedRequest->hasHeader('content-digest'));
    }

}
