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
        $certInstance = new CertHelper('ec_brainpoolP512t1');

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
            'public_key' => $configuration->getPublicPem(),
            'algorithm'  => 'sha512',
        ];
        $this->assertEquals(1, openssl_verify(...$signerDatas));

        /**
         * Now decrypt the Request
         */

        // first take the header and look for the signature and the signature-input

        $verifier = new Verify($signedRequest, $configuration->getPublicPem());

        $verifier->prepared();

        $cfg = $verifier->headerSignature->configuration;

        if ($cfg->metadata->tag) {
            $callable = fn(MessageInterface $message) => $message->getHeaderLine('x-Origin-host');

            // for mastodon an own config für x-origin or something
            $cfg->withPrepareComponentsMethods('@authority', $callable);
        }

        $this->assertEquals(1, $verifier->validate());
    }
}
