<?php

declare(strict_types=1);

namespace Filefabrik\HsapTest;

use DateTime;
use Filefabrik\Hsap\Signer;
use Filefabrik\Hsap\SignerConfiguration;
use Filefabrik\Hsap\Verify;
use Filefabrik\HsapTest\Tools\CertHelper;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Filefabrik\Hsap\Verify::class)]
final class VerifyTest extends TestCase
{
    public function testValidate(): void
    {
        /**
         *
         */
        $certInstance = new CertHelper('ec_brainpoolP512t1');

        /**
         * Prepare configuration
         */
        $currentDateTime = new DateTime()->getTimestamp();
        $configuration   = new SignerConfiguration();
        $configuration->withHeader('@method')
                      ->withHeader('x-Origins-host')
            /*
             * check duplicate content-digest
             * ->withHeader('content-digest')
             */

                      ->mustSignBody()
            //->withAlg('sha512')
                      ->withAlg('sha256')
                      ->withCreated($currentDateTime)
                      ->withExpires($currentDateTime + 7200)
                      ->withTag('mastodon')
                      ->withNonce('nonce-now')
                      ->withSignatureKeyName('testValue=')
            // for signing needs private
                      ->setPrivatePem($certInstance->getPrivatPemContent())
            // for verify needs public
                      ->setPublicPem($certInstance->getPublicPemContent())
        ;

        /**
         * Prepare the Request
         */
        $bodyParams = ['recipient' => 'Alex', 'amount' => 900000000, 'text' => 'fdawsfs'];
        $bodyJson   = json_encode($bodyParams, JSON_THROW_ON_ERROR);
        $request    =
            new Request(
                method: 'POST',
                uri: 'https://test',
                body: $bodyJson,
                headers: ['x-origin-host' => 'mytest.com'],
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
            'public_key' => $configuration->getPublicPem(),
            'algorithm'  => 'sha512',
        ];
        $this->assertEquals(1,openssl_verify(...$signerDatas));

        /**
         * Now decrypt the Request
         */

        // first take the header and look for the signature and the signature-input

        $verifier = new Verify($signedRequest, $configuration->getPublicPem());
        $this->assertEquals(1, $verifier->validate());

    }
}
