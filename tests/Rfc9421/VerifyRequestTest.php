<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test;

use BadMethodCallException;
use DateTime;
use Filefabrik\Rfc9421\Cryptography\SigningEnum;
use Filefabrik\Rfc9421\RequestSigner;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421\Support\DigestAlgorithmsEnum;
use Filefabrik\Rfc9421\VerifyPrepare;
use Filefabrik\Rfc9421\VerifyRequest;
use Filefabrik\Rfc9421Test\Tools\CertHelper;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use JsonException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;

/**
 * todo deeper checking
 */
#[CoversClass(VerifyRequest::class)]
final class VerifyRequestTest extends TestCase
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

    /**
     *
     * @throws \Bakame\Http\StructuredFields\StructuredFieldError
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidDerivedKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     * @throws JsonException
     */
    #[DataProvider('withSigningMethods')]
    public function testValidate(string|SigningEnum $signingAlgo, string $cert): void
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
        $bodyParams = ['recipient' => 'Alex', 'amount' => 900, 'currency' => 'EUR'];
        $bodyJson   = json_encode($bodyParams, JSON_THROW_ON_ERROR);
        $request    = new Request(
            method : 'POST',
            uri    : 'https://target-host.com',
            headers: ['x-origin-host' => 'target-host.com'],
            body   : $bodyJson,
        );
        /*
         * todo only with library Openssl test
         */
        if (
            PHP_VERSION < 8.5 && 'rsa_pss_sha512' === SigningEnum::isOwnTypeOrThrow($signingAlgo)
                                                                 ->getName()
        ) {
            $this->expectExceptionObject(
                new BadMethodCallException(
                    'rsa_pss_sha512 needs PHP 8.5+. Please see https://www.php.net/manual/en/openssl.padding.php#constant.openssl-pkcs1-pss-padding',
                ),
            );
        }

        $signer        = new RequestSigner($request, $configuration);
        $signedRequest = $signer->sign();

        /**
         * Now decrypt the Request
         */
        /**
         * optional we prepare a method that we inject if we need this
         */
        $mastodonVerifierMethods = [
            '@authority' => fn (MessageInterface $message): string => $message->getHeaderLine('x-Origin-host'),
        ];

        $tamperedRequest = clone $signedRequest;
        $tamperedBody    = $bodyParams;
        #$tamperedBody['mod'] = 'hasModiefied';
        $tamperedRequest = $tamperedRequest->withBody(Utils::streamFor(json_encode($tamperedBody)));

        $verifyPrepare = new VerifyPrepare($tamperedRequest, new SigningConfiguration());
        $verifyPrepare->prepareVerification();

        $verifier = new VerifyRequest($verifyPrepare);

        // optional before preparing verify and verifying
        $signerConfiguration = $verifier->signingConfiguration;
        //  ->setSignatureKeyName($signatureKeyName)
        // for verify needs public
        $signerConfiguration->setPublicPem($certInstance->getPublicPemContent());

        /*
         * now prepare the verify process
         */

        /*
         * Optional we have found a "tag" from sender.
         * Maybe that let us know, that we handle derivedComponents with a special method
         *
         */
        if (null !== $signerConfiguration->metadata->tag) {
            // for mastodon an own config für x-origin or something
            $signerConfiguration->addSigningMethods($mastodonVerifierMethods);
        }

        self::assertEquals(1, $verifier->verifying());
    }

    /**
     *
     * @throws \Bakame\Http\StructuredFields\StructuredFieldError
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidDerivedKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     * @throws JsonException
     */
    #[DataProvider('withSigningMethods')]
    public function testValidateWithoutBody(
        string|SigningEnum $signingAlgo,
        string $cert,
    ): void {
        $certInstance = new CertHelper($cert);

        $signatureKeyName = 'testing=';
        /**
         * Prepare signingConfiguration
         */
        $currentDateTime = new DateTime()->getTimestamp();
        $configuration   = new SigningConfiguration();
        $configuration->signHeader('@method')
                      ->signHeader('@authority')
                      ->setSigningAlgorithm($signingAlgo)
                      ->setCreated($currentDateTime)
                      ->setExpires($currentDateTime + 7200)
                      ->setTag('mastodon')
                      ->withNonce('nonce-now')
                      ->setSignatureKeyName($signatureKeyName)
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

        $signer = new RequestSigner($request, $configuration);

        /*
         * todo only with library Openssl test
         */
        if (
            PHP_VERSION < 8.5 && 'rsa_pss_sha512' === SigningEnum::isOwnTypeOrThrow($signingAlgo)
                                                                 ->getName()
        ) {
            $this->expectExceptionObject(
                new BadMethodCallException(
                    'rsa_pss_sha512 needs PHP 8.5+. Please see https://www.php.net/manual/en/openssl.padding.php#constant.openssl-pkcs1-pss-padding',
                ),
            );
        }

        $signedRequest = $signer->sign();

        /**
         * Now decrypt the Request
         */
        /**
         * optional we prepare a method that we inject if we need this
         */
        $mastodonVerifierMethods = [
            '@authority' => fn (MessageInterface $message): string => $message->getHeaderLine('x-Origin-host'),
        ];

        $tamperedRequest     = clone $signedRequest;
        $tamperedBody        = $bodyParams;
        $tamperedBody['mod'] = 'hasModiefied';
        $tamperedRequest     = $tamperedRequest->withBody(
            body: Utils::streamFor(
                json_encode($tamperedBody, JSON_THROW_ON_ERROR),
            ),
        );
        $verifyPrepare       = new VerifyPrepare($tamperedRequest, new SigningConfiguration());
        $verifyPrepare->prepareVerification();

        $verifier = new VerifyRequest($verifyPrepare);

        // optional before preparing verify and verifying
        $signerConfiguration = $verifier->signingConfiguration;
        //  ->setSignatureKeyName($signatureKeyName)
        // for verify needs public
        $signerConfiguration->setPublicPem($certInstance->getPublicPemContent());

        /*
         * Optional we have found a "tag" from sender.
         * Maybe that let us know, that we handle derivedComponents with a special method
         *
         */
        if (null !== $signerConfiguration->metadata->tag) {
            // for mastodon an own config für x-origin or something
            $signerConfiguration->addSigningMethods($mastodonVerifierMethods);
        }

        self::assertEquals(1, $verifier->verifying());
    }

    /**
     *
     * @throws \Bakame\Http\StructuredFields\StructuredFieldError
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidDerivedKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     * @throws JsonException
     */
    #[DataProvider('withSigningMethods')]
    public function testValidateWithContentDigest(string|SigningEnum $signingAlgo, string $cert): void
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
                      ->setContentDigestAlgo(contentDigestAlgo: DigestAlgorithmsEnum::sha512)
            /*
             * check duplicate content-digest
             * ->withHeader('content-digest')
             */
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
        $bodyParams = ['recipient' => 'Alex', 'amount' => 900, 'currency' => 'EUR'];
        $bodyJson   = json_encode($bodyParams, JSON_THROW_ON_ERROR);
        $request    = new Request(
            method : 'POST',
            uri    : 'https://target-host.com',
            headers: ['x-origin-host' => 'target-host.com'],
            body   : $bodyJson,
        );
        /*
         * todo only with library Openssl test
         */
        if (
            PHP_VERSION < 8.5 && 'rsa_pss_sha512' === SigningEnum::isOwnTypeOrThrow($signingAlgo)
                                                                 ->getName()
        ) {
            $this->expectExceptionObject(
                new BadMethodCallException(
                    'rsa_pss_sha512 needs PHP 8.5+. Please see https://www.php.net/manual/en/openssl.padding.php#constant.openssl-pkcs1-pss-padding',
                ),
            );
        }

        $signer        = new RequestSigner($request, $configuration);
        $signedRequest = $signer->sign();

        /**
         * Now decrypt the Request
         */
        $verifyPrepare = new VerifyPrepare($signedRequest, new SigningConfiguration());
        $verifyPrepare->prepareVerification();

        $verifier = new VerifyRequest($verifyPrepare);

        // optional before preparing verify and verifying
        $signerConfiguration = $verifier->signingConfiguration;
        //  ->setSignatureKeyName($signatureKeyName)
        // for verify needs public
        $signerConfiguration->setPublicPem($certInstance->getPublicPemContent());

        /*
         * now prepare the verify process
         */

        self::assertEquals(1, $verifier->verifying());
    }

    /**
     * todo with tampered body
     *
     *
     * @throws \Bakame\Http\StructuredFields\StructuredFieldError
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidDerivedKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     * @throws JsonException
     */
    #[DataProvider('withSigningMethods')]
    public function testValidateTemperedBody(
        string|SigningEnum $signingAlgo,
        string $cert,
    ): void {
        $certInstance = new CertHelper($cert);

        $signatureKeyName = 'testing=';
        /**
         * Prepare signingConfiguration
         */
        $currentDateTime = new DateTime()->getTimestamp();
        $configuration   = new SigningConfiguration();

        $configuration->signHeader('@method')
                      ->signHeader('@authority')
                      ->setSigningAlgorithm($signingAlgo)
                      ->setCreated($currentDateTime)
                      ->setExpires($currentDateTime + 7200)
                      ->setTag('mastodon')
                      ->withNonce('nonce-now')
                      ->setSignatureKeyName($signatureKeyName)
                      ->setContentDigestAlgo('sha256')

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
        /*
         * todo only with library Openssl test
         */
        if (
            PHP_VERSION < 8.5 && 'rsa_pss_sha512' === SigningEnum::isOwnTypeOrThrow($signingAlgo)
                                                                 ->getName()
        ) {
            $this->expectExceptionObject(
                new BadMethodCallException(
                    'rsa_pss_sha512 needs PHP 8.5+. Please see https://www.php.net/manual/en/openssl.padding.php#constant.openssl-pkcs1-pss-padding',
                ),
            );
        }

        $signer        = new RequestSigner($request, $configuration);
        $signedRequest = $signer->sign();

        /**
         * Now decrypt the Request
         */
        /**
         * optional we prepare a method that we inject if we need this
         */
        $mastodonVerifierMethods = [
            '@authority' => fn (MessageInterface $message): string => $message->getHeaderLine('x-Origin-host'),
        ];

        $tamperedRequest     = clone $signedRequest;
        $tamperedBody        = $bodyParams;
        $tamperedBody['mod'] = 'hasModiefied';
        $tamperedRequest     = $tamperedRequest->withBody(Utils::streamFor(json_encode($tamperedBody)));
        $verifyPrepare       = new VerifyPrepare($tamperedRequest, new SigningConfiguration());
        $verifyPrepare->prepareVerification();

        $verifier = new VerifyRequest($verifyPrepare);

        // optional before preparing verify and verifying
        $signerConfiguration = $verifier->signingConfiguration;
        //  ->setSignatureKeyName($signatureKeyName)
        // for verify needs public
        $signerConfiguration->setPublicPem($certInstance->getPublicPemContent());

        /*
         * now prepare the verify process
         */

        /*
         * Optional we have found a "tag" from sender.
         * Maybe that let us know, that we handle derivedComponents with a special method
         *
         */
        if (null !== $signerConfiguration->metadata->tag) {
            // for mastodon an own config für x-origin or something
            $signerConfiguration->addSigningMethods($mastodonVerifierMethods);
        }

        self::assertFalse($verifier->verifying());
    }

    public function testGetMessage(): void
    {
        $request       = new Request('GET', 'https://target-host.com?play=something');
        $verifyPrepare = new VerifyPrepare($request, new SigningConfiguration());

        self::assertSame(
            'https://target-host.com?play=something',
            new VerifyRequest($verifyPrepare)->getMessage()
                                             ->getUri()
                                             ->__toString(),
        );
    }
}
