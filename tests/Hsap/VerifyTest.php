<?php

declare(strict_types=1);

namespace Filefabrik\HsapTest;

use DateTime;
use Filefabrik\Hsap\Exceptions\AlreadySetException;
use Filefabrik\Hsap\Exceptions\ContentDigestException;
use Filefabrik\Hsap\Exceptions\InvalidDerivedKeyException;
use Filefabrik\Hsap\Exceptions\InvalidHeaderKeyException;
use Filefabrik\Hsap\Exceptions\VerifyHeaderSignatureException;
use Filefabrik\Hsap\Signer;
use Filefabrik\Hsap\SignerConfiguration;
use Filefabrik\Hsap\Verify;
use Filefabrik\HsapTest\Tools\CertHelper;
use GuzzleHttp\Psr7\Request;
use JsonException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;

#[CoversClass(\Filefabrik\Hsap\Verify::class)]
final class VerifyTest extends TestCase
{
    /**
     * @throws InvalidHeaderKeyException
     * @throws InvalidDerivedKeyException
     * @throws ContentDigestException
     * @throws VerifyHeaderSignatureException
     * @throws JsonException
     * @throws AlreadySetException
     */
    public function testValidate(): void
    {
        /**
         *
         */
        $certInstance     = new CertHelper('ec_brainpoolP512t1');
        $algo             = 'sha512';
        $signatureKeyName = 'testing=';
        /**
         * Prepare configuration
         */
        $currentDateTime = new DateTime()->getTimestamp();
        $configuration   = new SignerConfiguration();
        $configuration->withHeader('@method')
            //    ->withHeader('x-Origin-host')
                      ->withHeader('@authority')
            /*
             * check duplicate content-digest
             * ->withHeader('content-digest')
             */

                      ->mustSignBody()

            //->withAlg('sha512')
                      ->withAlg($algo)
                      ->withCreated($currentDateTime)
                      ->withExpires($currentDateTime + 7200)
                      ->withTag('mastodon')
                      ->withNonce('nonce-now')
                      ->withSignatureKeyName($signatureKeyName)
            // for signing needs private
                      ->setPrivatePem($certInstance->getPrivatPemContent())
            // for verify needs public
                     // ->setPublicPem($certInstance->getPublicPemContent())
        ;

        /**
         * Prepare the Request
         */
        $bodyParams = ['recipient' => 'Alex', 'amount' => 900000000, 'text' => 'fdawsfs'];
        $bodyJson   = json_encode($bodyParams, JSON_THROW_ON_ERROR);
        $request    =
            new Request(
                method : 'POST',
                uri    : 'https://test.com.com',
                headers: ['x-origin-host' => 'test.com.com'],
                body   : $bodyJson,
            );

        $signer        = new Signer($request, $configuration);
        $signedRequest = $signer->sign();

        /**
         * for testing
         */
        $decoded     = base64_decode($signer->base64_signature);
        $signerDatas = [
            'data'       => $signer->signingParametersString,
            'signature'  => $decoded,
            'public_key' => $certInstance->getPublicPemContent(),
            'algorithm'  => $algo,
        ];
        $this->assertEquals(1, openssl_verify(...$signerDatas));

        /**
         * Now decrypt the Request
         */

        // first take the header and look for the signature and the signature-input

        $verifier = new Verify($signedRequest);
        // optional
        $signerConfiguration = $verifier->signerConfiguration;
        //  ->withSignatureKeyName($signatureKeyName)
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
            $signerConfiguration->withPrepareComponentsMethods(
                [
                    ['@authority', fn(MessageInterface $message) => $message->getHeaderLine('x-Origin-host'),],
                ],
            );
        }

        $this->assertEquals(1, $verifier->validate());
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
        new Verify($r)->validate();
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
        new Verify($r)->validate();
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
        $instance->validate();
    }
}
