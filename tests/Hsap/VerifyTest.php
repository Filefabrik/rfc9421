<?php

declare(strict_types=1);

namespace Filefabrik\HsapTest;

use DateTime;
use Filefabrik\Hsap\Cryptography\SigningEnum;
use Filefabrik\Hsap\Exceptions\AlreadySetException;
use Filefabrik\Hsap\Exceptions\ContentDigestException;
use Filefabrik\Hsap\Exceptions\InvalidDerivedKeyException;
use Filefabrik\Hsap\Exceptions\InvalidHeaderKeyException;
use Filefabrik\Hsap\Exceptions\SignatureException;
use Filefabrik\Hsap\Exceptions\VerifyHeaderSignatureException;
use Filefabrik\Hsap\Signer;
use Filefabrik\Hsap\SigningConfiguration;
use Filefabrik\Hsap\Verify;
use Filefabrik\HsapTest\Tools\CertHelper;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use JsonException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;

#[CoversClass(Verify::class)]
final class VerifyTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        require_once dirname(__DIR__, 2).'/src/register-openssl-signing-verify.php';
    }

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
     * @throws AlreadySetException
     * @throws ContentDigestException
     * @throws InvalidDerivedKeyException
     * @throws InvalidHeaderKeyException
     * @throws JsonException
     * @throws VerifyHeaderSignatureException
     * @throws SignatureException
     *
     */
    #[DataProvider('withSigningMethods')]
    public function testValidate($signingAlgo, $cert): void
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
        $configuration->withHeader('@method')
            //    ->withHeader('x-Origin-host')
                      ->withHeader('@authority')
            /*
             * check duplicate content-digest
             * ->withHeader('content-digest')
             */

                      ->mustSignBody()

            //->withAlg('sha512')
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

        $signer        = new Signer($request, $configuration);
        $signedRequest = $signer->sign();

        /**
         * Now decrypt the Request
         */
        /**
         * optional we prepare a method that we inject if we need this
         */
        $mastodonVerifierMethods = [
            ['@authority', fn(MessageInterface $message) => $message->getHeaderLine('x-Origin-host'),],
        ];

        $tamperedRequest = clone $signedRequest;
        $tamperedBody    = $bodyParams;
        #$tamperedBody['mod'] = 'hasModiefied';
        $tamperedRequest = $tamperedRequest->withBody(Utils::streamFor(json_encode($tamperedBody)));
        $verifier        = new Verify($tamperedRequest);

        // optional before preparing verify and verifying
        $signerConfiguration = $verifier->signingConfiguration;
        //  ->withSignatureKeyName($signatureKeyName)
        // for verify needs public
        $signerConfiguration->setPublicPem($certInstance->getPublicPemContent());

        /**
         * now prepare the verify process
         */
        $verifier->prepareVerify();

        /*
         * Optional we have found a "tag" from sender.
         * Maybe that let us know, that we handle derivedComponents with a special method
         *
         */
        if ($signerConfiguration->metadata->tag) {
            // for mastodon an own config für x-origin or something
            $signerConfiguration->withPrepareComponentsMethods($mastodonVerifierMethods);
        }

        $this->assertEquals(1, $verifier->verifying());
    }

    /**
     * @throws AlreadySetException
     * @throws ContentDigestException
     * @throws InvalidDerivedKeyException
     * @throws InvalidHeaderKeyException
     * @throws JsonException
     * @throws SignatureException
     * @throws VerifyHeaderSignatureException
     */
    #[DataProvider('withSigningMethods')]
    public function testValidateWithoutBody($signingAlgo, $cert): void
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
        $configuration->withHeader('@method')
            //    ->withHeader('x-Origin-host')
                      ->withHeader('@authority')
            /*
             * check duplicate content-digest
             * ->withHeader('content-digest')
             */

                      ->mustSignBody(false)

            //->withAlg('sha512')
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

        $signer        = new Signer($request, $configuration);
        $signedRequest = $signer->sign();

        /**
         * Now decrypt the Request
         */
        /**
         * optional we prepare a method that we inject if we need this
         */
        $mastodonVerifierMethods = [
            ['@authority', fn(MessageInterface $message) => $message->getHeaderLine('x-Origin-host'),],
        ];

        $tamperedRequest     = clone $signedRequest;
        $tamperedBody        = $bodyParams;
        $tamperedBody['mod'] = 'hasModiefied';
        $tamperedRequest     = $tamperedRequest->withBody(Utils::streamFor(json_encode($tamperedBody)));
        $verifier            = new Verify($tamperedRequest);

        // optional before preparing verify and verifying
        $signerConfiguration = $verifier->signingConfiguration;
        //  ->withSignatureKeyName($signatureKeyName)
        // for verify needs public
        $signerConfiguration->setPublicPem($certInstance->getPublicPemContent());

        /**
         * now prepare the verify process
         */
        $verifier->prepareVerify();

        /*
         * Optional we have found a "tag" from sender.
         * Maybe that let us know, that we handle derivedComponents with a special method
         *
         */
        if ($signerConfiguration->metadata->tag) {
            // for mastodon an own config für x-origin or something
            $signerConfiguration->withPrepareComponentsMethods($mastodonVerifierMethods);
        }

        $this->assertEquals(1, $verifier->verifying());
    }

    public function testValidateThrowMissingSignature(): void
    {
        $this->expectExceptionObject(new VerifyHeaderSignatureException('Missing signature'));
        $r = new Request(
            method : 'POST',
            uri    : 'https://test',
            headers: [
            ],
        );
        new Verify($r)->verifying();
    }

    public function testValidateThrowMissingSignatureInput(): void
    {
        $this->expectExceptionObject(new VerifyHeaderSignatureException('Missing signature-input'));
        $r = new Request(
            method : 'POST',
            uri    : 'https://test',
            headers: [
                'signature' => 'test=:crypt:',
            ],
        );
        new Verify($r)->verifying();
    }

    public function testValidateThrowHeaderKeyName(): void
    {
        $this->expectExceptionObject(
            new VerifyHeaderSignatureException('Error during extracting the signatureKeyName'),
        );
        $r        = new Request(
            method : 'POST',
            uri    : 'https://test',
            headers: [
                'signature'       => 'crypt:',
                'signature-input' => 'atmNotRelevant',
            ],
        );
        $instance = new Verify($r);
        $instance->verifying();
    }
    /**
     * todo with tampered body
     */
    /**
     * @throws AlreadySetException
     * @throws ContentDigestException
     * @throws InvalidDerivedKeyException
     * @throws InvalidHeaderKeyException
     * @throws JsonException
     * @throws SignatureException
     * @throws VerifyHeaderSignatureException
     */
    #[DataProvider('withSigningMethods')]
    public function testValidateTemperedBody($signingAlgo, $cert): void
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
        $configuration->withHeader('@method')
            //    ->withHeader('x-Origin-host')
                      ->withHeader('@authority')
            /*
             * check duplicate content-digest
             * ->withHeader('content-digest')
             */

                      ->mustSignBody()

            //->withAlg('sha512')
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

        $signer        = new Signer($request, $configuration);
        $signedRequest = $signer->sign();

        /**
         * Now decrypt the Request
         */
        /**
         * optional we prepare a method that we inject if we need this
         */
        $mastodonVerifierMethods = [
            ['@authority', fn(MessageInterface $message) => $message->getHeaderLine('x-Origin-host'),],
        ];

        $tamperedRequest     = clone $signedRequest;
        $tamperedBody        = $bodyParams;
        $tamperedBody['mod'] = 'hasModiefied';
        $tamperedRequest     = $tamperedRequest->withBody(Utils::streamFor(json_encode($tamperedBody)));
        $verifier            = new Verify($tamperedRequest);

        // optional before preparing verify and verifying
        $signerConfiguration = $verifier->signingConfiguration;
        //  ->withSignatureKeyName($signatureKeyName)
        // for verify needs public
        $signerConfiguration->setPublicPem($certInstance->getPublicPemContent());

        /**
         * now prepare the verify process
         */
        $verifier->prepareVerify();

        /*
         * Optional we have found a "tag" from sender.
         * Maybe that let us know, that we handle derivedComponents with a special method
         *
         */
        if ($signerConfiguration->metadata->tag) {
            // for mastodon an own config für x-origin or something
            $signerConfiguration->withPrepareComponentsMethods($mastodonVerifierMethods);
        }

        $this->assertEquals(false, $verifier->verifying());
    }
}
