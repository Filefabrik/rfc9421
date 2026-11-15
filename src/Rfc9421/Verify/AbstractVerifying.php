<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Verify;

use Filefabrik\Rfc9421\Cryptography\VerifyRegistry;
use Filefabrik\Rfc9421\Exceptions\ContentDigestException;
use Filefabrik\Rfc9421\Exceptions\ProcessingException;
use Filefabrik\Rfc9421\RequestSigner;
use Filefabrik\Rfc9421\ResponseSigner;
use Filefabrik\Rfc9421\Signer\Signatures\ReadLines;
use Filefabrik\Rfc9421\SigningConfiguration;

abstract class AbstractVerifying
{
    /**
     * Flag if the body was part of the requester signing and the body has not tampered
     *
     */
    private(set) bool $hasValidatedBody = false;

    protected ?SigningConfiguration $signingConfiguration = null;

    /**
     * @var \Filefabrik\Rfc9421\Signer\Signatures\ReadLines|null null on not set
     */
    protected null|ReadLines $signedHeaderLines = null;

    /**
     * Main method to validate
     *
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
    public function verifying(): bool
    {
        $signingConfiguration = $this->getSigningConfiguration();
        $signingConfiguration->setSigningComponents(
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

        $verified = VerifyRegistry::verifying(
            signingEnum: $signingConfiguration->getMetadata()
                                              ->getAlgOrThrow(),
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

        return $verifyInstance->verify();
        // rebind the extracted php hash-algo
        // later in verify process, during rebuild the content-digest the algo is needed
        /*$this->getSigningConfiguration()
             ->setContentDigestAlgo(
                 contentDigestAlgo: $verifyInstance->getContentDigestVerifier()->phpAlgorithm,
             )
        ;*/
    }

    public function getHeaderHelper(): HeaderHelper
    {
        return $this->preparedVerify->getHeaderHelper();
    }

    abstract protected function getSigner(): RequestSigner|ResponseSigner;

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    private function getBinarySignatureValue(): string
    {
        return $this->preparedVerify->getHeaderSignatureExtractor()
                                    ->getBinarySignatureValue()
        ;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ProcessingException
     */
    public function getSignedHeaderLines(): ReadLines
    {
        return $this->signedHeaderLines ?? throw ProcessingException::SignedHeaderLines();
    }

    abstract public function getMessage();

}
