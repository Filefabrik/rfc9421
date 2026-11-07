<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421;

use Filefabrik\Rfc9421\Cryptography\VerifyRegistry;
use Filefabrik\Rfc9421\Exceptions\ContentDigestException;
use Filefabrik\Rfc9421\Exceptions\ProcessingException;
use Filefabrik\Rfc9421\Signer\Signatures\ReadLines;
use Filefabrik\Rfc9421\Verify\HeaderHelper;
use Filefabrik\Rfc9421\Verify\VerifyContentDigest;
use Psr\Http\Message\RequestInterface;

/**
 * Verification Process of incoming Request
 */
final class VerifyRequest
{
    /**
     * Flag if the body was part of the requester signing and the body has not tampered
     *
     */
    private(set) bool $hasValidatedBody = false;

    /**
     * @var \Filefabrik\Rfc9421\Signer\Signatures\ReadLines|null null on not set
     */
    private null|ReadLines $signedHeaderLines = null;

    private ?SigningConfiguration $signingConfiguration = null;

    public function __construct(private(set) readonly VerifyPrepare $preparedVerify)
    {
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ProcessingException
     */
    public function getSignedHeaderLines(): ReadLines
    {
        return $this->signedHeaderLines ?? throw ProcessingException::SignedHeaderLines();
    }

    /**
     * Main method to validate
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\EmptyException
     */
    public function verifying(): bool
    {
        $signingConfiguration = $this->getSigningConfiguration();
        $signingConfiguration->setComponents(
            components: $this->preparedVerify->getSignatureInputInstance()
                                             ->getExtractedComponents(),
        );

        // Validates the Content digest if needed from signature-line sig1=(content-digest)
        if ($signingConfiguration->hasContentDigestHashAlgo()) {
            if (!$this->validateContentDigest()) {
                // todo logging the error that validate content digest is wrong
                return false;
            }

            $this->hasValidatedBody = true;
        }

        $signer = $this->getSigner();

        // base_decoded signature to get binary

        $verified = VerifyRegistry::verifying(
            signingEnum: $signingConfiguration->metadata->getAlgOrThrow(),
            data       : $signer->getSigningParametersStringOrThrow(),
            signature  : $this->getBinarySignatureValue(),
            publicKey  : $signingConfiguration->getPublicPemOrThrow(),
        );

        if ($verified) {
            // todo get also back if not verified for diagnostics
            // getting back validated parameters non-tempered parameters
            $this->signedHeaderLines = new ReadLines($signer->getSignedLines());
        }

        return $verified;
    }

    public function getSigningConfiguration(): SigningConfiguration
    {
        return $this->signingConfiguration ??= $this->preparedVerify->signingConfiguration;
    }

    /**
     * Validates the content-digest if the content-digest was created by the requester
     *
     * @throws ContentDigestException
     */
    private function validateContentDigest(): bool
    {
        $verifyInstance = new VerifyContentDigest(
            contentDigest: $this->getHeaderHelper()
                                ->getContentDigestLine(),
            body         : (string)$this->preparedVerify->message
                ->getBody(),
        );
        if ($verified = $verifyInstance->verify()) {
            // rebind the extracted php hash-algo
            // later in verify process, during rebuild the content-digest the algo is needed
            $this->getSigningConfiguration()
                 ->setContentDigestAlgo(
                     contentDigestAlgo: $verifyInstance->getContentDigestVerifier()->phpAlgorithm,
                 )
            ;
        }

        // no validation of the body
        return $verified;
    }

    public function getHeaderHelper(): HeaderHelper
    {
        return $this->preparedVerify->getHeaderHelper();
    }

    /**
     * Getting the Signer instance to rebuild the received message
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     */
    private function getSigner(): RequestSigner
    {
        $signer = new RequestSigner(
            message             : clone $this->preparedVerify->message,
            signingConfiguration: $this->getSigningConfiguration(),
        );

        $signer->prepareSigning();

        return $signer;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    private function getBinarySignatureValue(): string
    {
        return $this->preparedVerify->getHeaderSignatureExtractor()
                                    ->getBinarySignatureValue()
        ;
    }

    public function getMessage(): RequestInterface
    {
        return $this->preparedVerify->message;
    }

}
