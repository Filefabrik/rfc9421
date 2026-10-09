<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test;

use BadMethodCallException;
use DateTime;
use Filefabrik\Rfc9421\Cryptography\SigningEnum;
use Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException;
use Filefabrik\Rfc9421\Signer;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421\Support\DigestAlgorithmsEnum;
use Filefabrik\Rfc9421\Verify\HeaderHelper;
use Filefabrik\Rfc9421\Verify\HeaderSignatureInput;
use Filefabrik\Rfc9421\VerifyPrepare;
use Filefabrik\Rfc9421Test\Tools\CertHelper;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(VerifyPrepare::class)]
class VerifyPrepareTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        require_once dirname(__DIR__, 2).'/src/register-openssl-signing-verify.php';
    }

    /**
     * @return array<string,string>
     */
    public static function withSigningMethods(): array
    {
        return [
            [SigningEnum::rsa_pss_sha512->name, 'rsa'],
            [SigningEnum::rsa_v_1_5_sha_256->name, 'rsa'],
            [SigningEnum::rsa_v_1_5_sha_384->name, 'rsa'],
            [SigningEnum::rsa_v_1_5_sha_512->name, 'rsa'],
            [SigningEnum::hmac_sha_256->name, 'hmac'],
            [SigningEnum::hmac_sha_384->name, 'hmac'],
            [SigningEnum::hmac_sha_512->name, 'hmac'],
            [SigningEnum::ecdsa_p_256_sha_256, 'ecdsaP256'],
            [SigningEnum::ecdsa_p_384_sha_384, 'ecdsaP384'],
            [SigningEnum::ed25519, 'ed25519'],
        ];
    }

    #[DataProvider('withSigningMethods')]
    public function testPrepareVerify(string|SigningEnum $signingAlgo, string $cert): void
    {
        $certInstance = new CertHelper($cert);

        $signatureKeyName = 'testing=';
        /**
         * Prepare signingConfiguration
         */
        $currentDateTime = new DateTime()->getTimestamp();
        $configuration   = new SigningConfiguration();
        $configuration->signHeader('@method')
                      ->signHeader('@authority')
            /*
             * check duplicate content-digest
             * ->withHeader('content-digest')
             */
                      ->withSigningAlgorithm($signingAlgo)
                      ->withCreated($currentDateTime)
                      ->withExpires($currentDateTime + 7200)
                      ->withTag('mastodon')
                      ->withNonce('nonce-now')
                      ->withSignatureKeyName($signatureKeyName)
            // for signing needs private
                      ->setPrivatePem($certInstance->getPrivatPemContent())
        ;

        /**
         * Prepare the Request
         */
        $bodyParams = ['recipient' => 'Alex', 'amount' => 900, 'currency' => 'EUR'];
        $bodyJson   = json_encode($bodyParams, JSON_THROW_ON_ERROR);
        $request    = new Request(
            method : 'POST',
            uri    : 'https://target-host.com',
            headers: ['x-origin-host' => 'target-host.com'],
            body   : $bodyJson,
        );
        /**
         * todo only with library Openssl test
         */
        if (PHP_VERSION < 8.5 && 'rsa_pss_sha512' === SigningEnum::isOwnType($signingAlgo)->name) {
            $this->expectExceptionObject(
                new BadMethodCallException(
                    'rsa_pss_sha512 needs PHP 8.5+. Please see https://www.php.net/manual/en/openssl.padding.php#constant.openssl-pkcs1-pss-padding',
                ),
            );
        }

        $signer        = new Signer($request, $configuration);
        $signedRequest = $signer->sign();

        $prepareVerifierInstance = new VerifyPrepare(
            message             : $signedRequest,
            signingConfiguration: new SigningConfiguration(),

        );

        /**
         * now prepare the verify process
         */
        self::assertTrue($prepareVerifierInstance->prepareVerification());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidDerivedKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \JsonException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    #[DataProvider('withSigningMethods')]
    public function testPrepareVerifyWithContentDigest(string|SigningEnum $signingAlgo, string $cert): void
    {
        /**
         *
         */
        $certInstance = new CertHelper($cert);

        $signatureKeyName = 'testing=';
        /**
         * Prepare signingConfiguration
         */
        $currentDateTime = new DateTime()->getTimestamp();
        $configuration   = new SigningConfiguration();
        $configuration->signHeader('@method')
                      ->signHeader('@authority')
            /*
             * check duplicate content-digest
             * ->withHeader('content-digest')
             */
                      ->withSigningAlgorithm($signingAlgo)
                      ->withCreated($currentDateTime)
                      ->withExpires($currentDateTime + 7200)
                      ->withTag('mastodon')
                      ->withNonce('nonce-now')
                      ->withSignatureKeyName($signatureKeyName)
                      ->setContentDigestHashAlgo(DigestAlgorithmsEnum::sha512)
            // for signing needs private
                      ->setPrivatePem($certInstance->getPrivatPemContent())
        ;

        /**
         * Prepare the Request
         */
        $bodyParams = ['recipient' => 'Alex', 'amount' => 900, 'currency' => 'EUR'];
        $bodyJson   = json_encode($bodyParams, JSON_THROW_ON_ERROR);
        $request    = new Request(
            method : 'POST',
            uri    : 'https://target-host.com',
            headers: ['x-origin-host' => 'target-host.com'],
            body   : $bodyJson,
        );
        /**
         * todo only with library Openssl test
         */
        if (PHP_VERSION < 8.5 && 'rsa_pss_sha512' === SigningEnum::isOwnType($signingAlgo)->name) {
            $this->expectExceptionObject(
                new BadMethodCallException(
                    'rsa_pss_sha512 needs PHP 8.5+. Please see https://www.php.net/manual/en/openssl.padding.php#constant.openssl-pkcs1-pss-padding',
                ),
            );
        }

        $signer        = new Signer($request, $configuration);
        $signedRequest = $signer->sign();

        $prepareVerifierInstance = new VerifyPrepare(
            message             : $signedRequest,
            signingConfiguration: new SigningConfiguration(),

        );

        /**
         * now prepare the verify process
         */
        self::assertTrue($prepareVerifierInstance->prepareVerification());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public function testValidateThrowMissingSignatureInput(): void
    {
        $this->expectExceptionObject(new VerifyHeaderSignatureException('Missing signature-input'));
        $r                       = new Request(
            method : 'POST',
            uri    : 'https://test',
            headers: [
                'signature' => 'test=:crypt:',
            ],
        );
        $prepareVerifierInstance = new VerifyPrepare(
            message             : $r,
            signingConfiguration: new SigningConfiguration(),
        );

        /**
         * now prepare the verify process
         */
        self::assertTrue($prepareVerifierInstance->prepareVerification());
    }

    public function testValidateThrowMissingSignature(): void
    {
        $this->expectExceptionObject(new VerifyHeaderSignatureException('Missing signature'));
        $r                       = new Request(
            method : 'POST',
            uri    : 'https://test',
            headers: [
            ],
        );
        $prepareVerifierInstance = new VerifyPrepare(
            message             : $r,
            signingConfiguration: new SigningConfiguration(),

        );

        /**
         * now prepare the verify process
         */
        self::assertTrue($prepareVerifierInstance->prepareVerification());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     */
    public function testGetSignatureInputInstanceThrow(): void
    {
        $this->expectExceptionObject(new VerifyHeaderSignatureException('missing header signature'));
        $r                       = new Request(
            method : 'POST',
            uri    : 'https://test',
            headers: [
                'signature' => 'test=:crypt:',
            ],
        );
        $prepareVerifierInstance = new VerifyPrepare(
            message             : $r,
            signingConfiguration: new SigningConfiguration(),
        );
        $prepareVerifierInstance->getSignatureInputInstance();
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidDerivedKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     * @throws \JsonException
     */
    public function testGetSignatureInputInstance(): void
    {
        $certInstance = new CertHelper('rsa');

        $signatureKeyName = 'testing=';
        $bodyParams       = ['recipient' => 'Alex', 'amount' => 900, 'currency' => 'EUR'];
        $bodyJson         = json_encode($bodyParams, JSON_THROW_ON_ERROR);
        $request          = new Request(
            method : 'POST',
            uri    : 'https://target-host.com',
            headers: ['x-origin-host' => 'target-host.com'],
            body   : $bodyJson,
        );
        $currentDateTime  = new DateTime()->getTimestamp();
        $configuration    = new SigningConfiguration();
        $configuration->signHeader('@method')
                      ->signHeader('@authority')
            /*
             * check duplicate content-digest
             * ->withHeader('content-digest')
             */
                      ->withSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_256)
                      ->withCreated($currentDateTime)
                      ->withExpires($currentDateTime + 7200)
                      ->withTag('mastodon')
                      ->withNonce('nonce-now')
                      ->withSignatureKeyName($signatureKeyName)
                      ->setContentDigestHashAlgo(DigestAlgorithmsEnum::sha512)
            // for signing needs private
                      ->setPrivatePem($certInstance->getPrivatPemContent())
        ;

        $signer                  = new Signer($request, $configuration);
        $signedRequest           = $signer->sign();
        $prepareVerifierInstance = new VerifyPrepare(
            message             : $signedRequest,
            signingConfiguration: new SigningConfiguration(),
        );
        $prepareVerifierInstance->prepareVerification();
        self::assertInstanceOf(HeaderSignatureInput::class, $prepareVerifierInstance->getSignatureInputInstance());
    }

    public function testGetHeaderHelper(): void
    {
        $prepareVerifierInstance = new VerifyPrepare(
            message             : new Request('post','https://test'),
            signingConfiguration: new SigningConfiguration(),
        );
        self::assertInstanceOf(HeaderHelper::class,$prepareVerifierInstance->getHeaderHelper());
    }



}
