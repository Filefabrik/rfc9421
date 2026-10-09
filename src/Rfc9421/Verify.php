<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421;

use Filefabrik\Rfc9421\Cryptography\SigningEnum;
use Filefabrik\Rfc9421\Cryptography\VerifyRegistry;
use Filefabrik\Rfc9421\Exceptions\AlreadySetException;
use Filefabrik\Rfc9421\Exceptions\ContentDigestException;
use Filefabrik\Rfc9421\Exceptions\InvalidDerivedKeyException;
use Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException;
use Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException;
use Filefabrik\Rfc9421\Signer\SignatureParams;
use Filefabrik\Rfc9421\Signer\Signatures\ReadLines;
use Filefabrik\Rfc9421\Verify\HeaderHelper;
use Filefabrik\Rfc9421\Verify\HeaderSignatureInput;
use Filefabrik\Rfc9421\Verify\VerifyContentDigest;
use Psr\Http\Message\RequestInterface;

final class Verify
{

    /**
     * @var \Filefabrik\Rfc9421\Signer\Signatures\ReadLines|null null on not set
     */
    private(set) null|ReadLines $signedHeaderLines = null;

    /**
     * Extracting the signature and split into parts
     */
    private(set) Extractors\HeaderSignature $headerSignatureExtractor;

    private(set) SigningConfiguration $signingConfiguration;
    /**
     * @var \Filefabrik\Rfc9421\Verify\HeaderSignatureInput
     */
    private HeaderSignatureInput $signatureInputInstance;

    /**
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     */
    public function __construct(private(set) readonly VerifyPrepare $preparedVerify)
    {
        $this->signingConfiguration     = $this->preparedVerify->signingConfiguration;
        $this->signatureInputInstance   = $this->preparedVerify->getSignatureInputInstance();
        $this->headerSignatureExtractor = $this->preparedVerify->getHeaderSignatureExtractor();
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
        if ($this->signingConfiguration->hasContentDigestHashAlgo() && !$this->validateContentDigest()) {
            return false;
        }

        $signer = $this->getSigner();

        // base_decoded signature to get binary
        $sig = $this->headerSignatureExtractor
            ->getBinarySignatureValue()
        ;

        $verified = VerifyRegistry::verifying(
            signingEnum: SigningEnum::isOwnTypeOrThrow($this->signingConfiguration->metadata->alg),
            data       : $signer->getSigningParametersStringOrThrow(),
            signature  : $sig,
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
     */
    private function prepareSigningComponents(): void
    {
        // all keys they have into rebuildable signing array

        $signingComponents = SignatureParams::fromArray($this->signatureInputInstance->extractedComponents)
                                            ->getSignatureParameters()
        ;

        // no compile the signature array such as in singer
        foreach ($signingComponents as $signingComponent) {
            $this->signingConfiguration->signHeader($signingComponent);
        }
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
            body         : (string)$this->getMessage()
                                        ->getBody(),
        );
        if ($verified = $verifyInstance->verify()) {
            // rebind the extracted php hash-algo
            // later in verify process, during rebuild the content-digest the algo is needed
            $this->signingConfiguration->setContentDigestHashAlgo(
                contentDigestHashAlgo: $verifyInstance->getContentDigestVerifier()->phpAlgorithm,
            );
        }

        // no validation of the body
        return $verified;
    }

    public function getHeaderHelper(): HeaderHelper
    {
        return $this->preparedVerify->getHeaderHelper();
    }

    public function getMessage(): RequestInterface
    {
        return $this->preparedVerify->message;
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
            message             : clone $this->getMessage(),
            signingConfiguration: $this->signingConfiguration,
        );

        $signer->prepareSigning();

        return $signer;
    }

}
