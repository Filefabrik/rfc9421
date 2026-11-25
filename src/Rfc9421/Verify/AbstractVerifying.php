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
use Psr\Http\Message\MessageInterface;

abstract class AbstractVerifying
{
    /**
     * @var \Filefabrik\Rfc9421\Signer\Signatures\ReadLines|null null on not set
     */
    protected null|ReadLines $signedHeaderLines = null;

    /**
     * Flag if the body was part of the requester signing and the body has not tampered
     */
    private bool|null $hasSignedBody = null;

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
        $preparedSigningConfiguration = $this->getVerifySigningConfiguration();
        $preparedSigningConfiguration->setSigningComponents(
            components: $this->preparedVerify
                ->getSignatureInputInstance()
                ->getExtractedComponents(),
        );

        $contentDigestResult = $this->verifyContentDigestIfNeed($preparedSigningConfiguration);

        if (!$contentDigestResult) {
            // todo logging the error that validate content digest is wrong
            return false;
        }

        $signer = $this->getSigner();

        $verified = VerifyRegistry::verifying(
            signingEnumInterface: $preparedSigningConfiguration->getMetadata()
                                                               ->getAlgOrThrow(),
            data                : $signer->getSigningParametersStringOrThrow(),
            signature           : $this->getBinarySignatureValue(),
            publicKey           : $preparedSigningConfiguration->getPublicPemOrThrow(),
        );

        if ($verified) {
            // todo get also back if not verified for diagnostics
            // getting back validated parameters non-tempered parameters
            $this->signedHeaderLines = new ReadLines($signer->getSignedLines());
        }

        return $verified;
    }

    /**
     * From prepared SingingConfiguration
     */
    public function getVerifySigningConfiguration(): SigningConfiguration
    {
        return $this->preparedVerify->signingConfiguration;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     */
    private function verifyContentDigestIfNeed(SigningConfiguration $signingConfiguration): bool
    {
        // Validates the Content digest if needed from signature-line sig1=("content-digest")
        if ($signingConfiguration->hasContentDigestHashAlgo()) {
            if (!$this->validateContentDigest()) {
                // todo logging the error that validate content digest is wrong
                return $this->hasSignedBody = false;
            }

            // was signed and is valid
            $this->hasSignedBody = true;
        }

        if (null === $this->hasSignedBody) {
            // content-digest was not part of the signing process
            $this->hasSignedBody = false;
        }

        // return true so tell we have no validation error
        return true;
    }

    /**
     * Validates the content-digest if the content-digest was created by the requester
     *
     * @throws ContentDigestException
     */
    private function validateContentDigest(): bool
    {
        $contentDigestLine = $this->preparedVerify
            ->getHeaderHelper()
            ->getContentDigestLine()
        ;

        $verifyInstance = new VerifyContentDigest(
            contentDigest: $contentDigestLine,
            body         : (string)$this->preparedVerify->message
                ->getBody(),
        );

        return $verifyInstance->verify();
    }

    abstract protected function getSigner(): RequestSigner|ResponseSigner;

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    private function getBinarySignatureValue(): string
    {
        return $this->preparedVerify
            ->getHeaderSignatureExtractor()
            ->getBinarySignatureValue()
        ;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ProcessingException
     */
    public function getSignedHeaderLines(): ReadLines
    {
        if ($this->signedHeaderLines instanceof ReadLines) {
            return $this->signedHeaderLines;
        }
        throw ProcessingException::SignedHeaderLines();
    }

    abstract public function getMessage(): MessageInterface;

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ProcessingException
     */
    public function hasSignedBody(): bool
    {
        return $this->hasSignedBody ?? throw ProcessingException::hasSignedBodyBeforeVerify();
    }

}
