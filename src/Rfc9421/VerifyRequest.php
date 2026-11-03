<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421;

use Filefabrik\Rfc9421\Cryptography\VerifyRegistry;
use Filefabrik\Rfc9421\Exceptions\ContentDigestException;
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
     * @var \Filefabrik\Rfc9421\Signer\Signatures\ReadLines|null null on not set
     */
    private(set) null|ReadLines $signedHeaderLines = null;

    private(set) SigningConfiguration $signingConfiguration;

    private(set) bool $hasValidatedBody = false;

    public function __construct(private(set) readonly VerifyPrepare $preparedVerify)
    {
        $this->signingConfiguration = $this->preparedVerify->signingConfiguration;
    }

    /**
     * Main method to validate
     *
     * @throws \Bakame\Http\StructuredFields\StructuredFieldError
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     */
    public function verifying(): bool
    {
        $this->signingConfiguration->setComponents(
            components: $this->preparedVerify->getSignatureInputInstance()->getExtractedComponents(),
        );

        // Validates the Content digest if needed from signature-line sig1=(content-digest)
        if ($this->signingConfiguration->hasContentDigestHashAlgo()) {
            if (!$this->validateContentDigest()) {
                // todo logging the error that validate content digest is wrong
                return false;
            }

            $this->hasValidatedBody = true;
        }

        $signer = $this->getSigner();

        // base_decoded signature to get binary

        $verified = VerifyRegistry::verifying(
            signingEnum: $this->signingConfiguration->metadata->getAlgOrThrow(),
            data       : $signer->getSigningParametersStringOrThrow(),
            signature  : $this->getBinarySignatureValue(),
            publicKey  : $this->signingConfiguration->getPublicPem(),
        );

        if ($verified) {
            // getting back validated parameters non-tempered parameters
            $this->signedHeaderLines = new ReadLines($signer->getSignedLines());
        }

        return $verified;
    }

    /**
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
            $this->signingConfiguration->setContentDigestAlgo(
                contentDigestAlgo: $verifyInstance->getContentDigestVerifier()->phpAlgorithm,
            );
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
     * @throws \Bakame\Http\StructuredFields\StructuredFieldError
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     */
    private function getSigner(): RequestSigner
    {
        $signer = new RequestSigner(
            message             : clone $this->preparedVerify->message,
            signingConfiguration: $this->signingConfiguration,
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
