<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421;

use Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException;
use Filefabrik\Rfc9421\Extractors\ContentDigest;
use Filefabrik\Rfc9421\Extractors\HeaderSignature;
use Filefabrik\Rfc9421\Signer\Metadata;
use Filefabrik\Rfc9421\Verify\HeaderHelper;
use Filefabrik\Rfc9421\Verify\HeaderSignatureInput;
use Filefabrik\Rfc9421\Verify\PrepareSignatureKeyName;
use Psr\Http\Message\RequestInterface;

/**
 * Outside from the verify process read only the incoming request
 * After the prepareVerification method call, the request was fully "decoded"
 */
final class VerifyPrepare
{

    /**
     * @var ?\Filefabrik\Rfc9421\Extractors\HeaderSignature
     */
    private(set) ?HeaderSignature $headerSignatureExtractor = null;
    /**
     * @var null|\Filefabrik\Rfc9421\Verify\HeaderSignatureInput
     */
    private ?HeaderSignatureInput $signatureInputInstance = null;
    /**
     * @var \Filefabrik\Rfc9421\Verify\HeaderHelper
     */
    private HeaderHelper $headerHelper;

    public function __construct(
        private(set) readonly RequestInterface $message,
        private(set) readonly SigningConfiguration $signingConfiguration,

    ) {
        $this->headerHelper = new HeaderHelper($this->message);
    }

    public function getHeaderHelper(): HeaderHelper
    {
        return $this->headerHelper;
    }

    /**
     * First prepare some request datas
     * Throws if there is an error
     *
     * @throws VerifyHeaderSignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     */
    public function prepareVerification(): bool
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

        // only bool that there were no exceptions
        return true;
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

    /**
     * @return \Filefabrik\Rfc9421\Verify\HeaderSignatureInput
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     */
    public function getSignatureInputInstance(): HeaderSignatureInput
    {
        return $this->signatureInputInstance ?? throw new VerifyHeaderSignatureException('missing header signature');
    }
}
