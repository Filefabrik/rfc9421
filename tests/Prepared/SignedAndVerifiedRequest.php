<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Prepared;

use DateTime;
use Filefabrik\Rfc9421\Enums\DigestAlgorithmsEnum;
use Filefabrik\Rfc9421\Enums\SigningEnum;
use Filefabrik\Rfc9421\RequestSigner;
use Filefabrik\Rfc9421\SigningComponents;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421\VerifyRequest;
use Filefabrik\Rfc9421\VerifyRequestPrepare;
use Filefabrik\Rfc9421Test\Tools\CertHelper;
use GuzzleHttp\Psr7\Request;

final class SignedAndVerifiedRequest
{
    /**
     * Helper/Mock Method
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\ProcessingException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
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
    public static function get(): VerifyRequest
    {
        $cert         = 'rsa';
        $signingAlgo  = SigningEnum::rsa_v_1_5_sha_512->name;
        $certInstance = new CertHelper($cert);

        $signatureKeyName = 'testing=';

        $signedComponents = new SigningComponents()->sign('@method')
                                                   ->sign('@authority')
        ;
        /**
         * Prepare signingConfiguration
         */
        $currentDateTime = new DateTime()->getTimestamp();
        $configuration   = new SigningConfiguration();
        $configuration->setSigningComponents($signedComponents)
                      ->withContentDigest(contentDigestAlgo: DigestAlgorithmsEnum::sha512)
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
        $signerConfiguration = $verifier->getVerifySigningConfiguration();
        //  ->setSignatureKeyName($signatureKeyName)
        // for verify needs public
        $signerConfiguration->setPublicPem($certInstance->getPublicPemContent());

        /*
         * now prepare the verify process
         */

        $verifier->verifying();

        return $verifier;
    }
}
