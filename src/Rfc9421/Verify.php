<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421;

use Filefabrik\Rfc9421\Cryptography\VerifyRegistry;
use Filefabrik\Rfc9421\Exceptions\AlreadySetException;
use Filefabrik\Rfc9421\Exceptions\ContentDigestException;
use Filefabrik\Rfc9421\Exceptions\InvalidDerivedKeyException;
use Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException;
use Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException;
use Filefabrik\Rfc9421\Signer\SignatureParams;
use Filefabrik\Rfc9421\Signer\Signatures\ReadLines;
use Filefabrik\Rfc9421\Verify\HeaderHelper;
use Filefabrik\Rfc9421\Verify\VerifyContentDigest;
use Psr\Http\Message\RequestInterface;

/**
 * Verification Process of incoming Request
 */
final class Verify
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
     * @throws ContentDigestException
     * @throws InvalidHeaderKeyException
     * @throws VerifyHeaderSignatureException
     * @throws AlreadySetException
     * @throws InvalidDerivedKeyException|Exceptions\SignatureException|Exceptions\SignatureExtractionException
     */
    public function verifying(): bool
    {
        $this->prepareSigningComponents();

        // Validates the Content digest if needed from signature-line sig1=(content-digest)
        if ($this->signingConfiguration->hasContentDigestHashAlgo()) {
            if (!$this->validateContentDigest()) {
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
     * Extract which "Components" must be used to validate the header data
     *
     * @throws AlreadySetException
     * @throws InvalidDerivedKeyException
     * @throws InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     */
    private function prepareSigningComponents(): void
    {
        // all keys they have into rebuildable signing array

        // no compile the signature array such as in singer
        foreach ($this->getSigningComponents() as $signingComponent) {
            $this->signingConfiguration->signHeader($signingComponent);
        }
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidDerivedKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @return string[]
     */
    private function getSigningComponents(): array
    {
        return SignatureParams::fromArray($this->preparedVerify->getSignatureInputInstance()->extractedComponents)
                              ->getSignatureParameters()
        ;
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
     * @throws InvalidDerivedKeyException
     * @throws InvalidHeaderKeyException
     * @throws AlreadySetException
     * @throws ContentDigestException
     */
    private function getSigner(): Signer
    {
        $signer = new Signer(
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
