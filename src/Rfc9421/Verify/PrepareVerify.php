<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Verify;

use Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException;
use Filefabrik\Rfc9421\Extractors\ContentDigest;
use Filefabrik\Rfc9421\Extractors\HeaderSignature;
use Filefabrik\Rfc9421\Signer\Metadata;
use Filefabrik\Rfc9421\SigningConfiguration;
use Psr\Http\Message\RequestInterface;

/**
 * Outside from the verify process read only the incoming request
 */
final class PrepareVerify
{

    /**
     * @var null|\Filefabrik\Rfc9421\Verify\HeaderSignatureInput
     */
    private(set) ?HeaderSignatureInput $signatureInputInstance = null;
    /**
     * @var ?\Filefabrik\Rfc9421\Extractors\HeaderSignature
     */
    private ?HeaderSignature $headerSignatureExtractor = null;

    public function __construct(
        private(set) readonly RequestInterface $message,
        private(set) readonly SigningConfiguration $signingConfiguration,
        private(set) readonly HeaderHelper $headerHelper,
    ) {
    }

    /**
     * First prepare some request datas
     * Throws if there is an error
     *
     * @throws VerifyHeaderSignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     */
    public function prepareVerify(): void
    {
        // first stage we must have both signature and signature-input header
        if (
            !$this->headerHelper
                ->hasSignatureLine()
        ) {
            throw VerifyHeaderSignatureException::missingSignature();
        }

        // empty
        if (
            !$this->headerHelper
                ->hasSignatureInputLine()
        ) {
            throw VerifyHeaderSignatureException::missingSignatureInput();
        }

        /*
         * Extracts the signatureKeyName if not already set above.
         */
        new PrepareSignatureKeyName(
            signingConfiguration    : $this->signingConfiguration,
            headerSignatureExtractor: $this->getHeaderSignatureExtractor(),
        )->prepare();

        /**
         * Early step to prepare the signer configuration.
         * The signer configuration can be modified before validation
         *
         * @throws VerifyHeaderSignatureException
         */
        Metadata::bindMetadata(
            instance: $this->signingConfiguration->metadata,
            metadata: $this->getSignaturInputInstance()->extractedMetadata->toArray(),
        );

        /**
         * extract the content-digest if exists in header
         */
        if (
            $this->headerHelper
                ->hasContentDigestLine()
        ) {
            $this->signingConfiguration->setContentDigestHashAlgo(
                new ContentDigest(
                    $this->headerHelper
                        ->getContentDigestLine(),
                )->getAlgorithm(),
            );
        }
    }

    public function getHeaderSignatureExtractor(): HeaderSignature
    {
        return $this->headerSignatureExtractor ??= new HeaderSignature(
            signatureString: $this->headerHelper
                ->getSignatureLine(),
        );
    }

    /**
     * @throws VerifyHeaderSignatureException|\Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    private function getSignaturInputInstance(): HeaderSignatureInput
    {
        $headerSignatureInstance = new HeaderSignatureInput(
            headerSignatureInputValue: $this->headerHelper
                ->getSignatureInputLine(),
            signatureNameKey         : $this->signingConfiguration->signatureKeyName ?? '',
        );
        // throws error
        $headerSignatureInstance->extract();

        return $this->signatureInputInstance = $headerSignatureInstance;
    }
}
