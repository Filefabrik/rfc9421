<?php
/*
 * Copyright (c) 2026.  rt@filefabrik.com
 * proprietary
 * todo insert correct copyright
 */
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
use UnexpectedValueException;

/**
 * Outside from the verify process read only the incoming request
 * After the prepareVerification method call, the request was fully "decoded"
 */
final class VerifyPrepare
{
    private(set) ?HeaderSignature $headerSignatureExtractor = null;

    private ?HeaderSignatureInput $signatureInputInstance = null;

    private readonly HeaderHelper $headerHelper;

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
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
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

        /*
         * Early step to prepare the signer configuration.
         * The signer configuration can be modified before validation
         *
         * @throws VerifyHeaderSignatureException
         */
        Metadata::bindMetadata(
            instance: $this->signingConfiguration->metadata,
            metadata: $this->getExtractedMetadataArray(),
        );

        /*
         * extract the content-digest if exists in header
         */
        $this->prepareContentDigestIfNeed();

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
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @return array{}|array{created?: int, expires?: int, nonce?: string, keyid?: string, alg?: string, tag?: string}
     */
    private function getExtractedMetadataArray(): array
    {
        return $this->getSignaturInputInstance()->extractedMetadata?->toArray() ?? throw new UnexpectedValueException(
            'No Metadata Found',
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
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
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     */
    private function prepareContentDigestIfNeed(): void
    {
        if (
            $this->headerHelper
                ->hasContentDigestLine()
        ) {
            $this->signingConfiguration->setContentDigestAlgo(
                contentDigestAlgo: new ContentDigest(
                    contentDigest: $this->headerHelper
                        ->getContentDigestLine(),
                )->getAlgorithm(),
            );
        }
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     */
    public function getSignatureInputInstance(): HeaderSignatureInput
    {
        return $this->signatureInputInstance ?? throw new VerifyHeaderSignatureException('missing header signature');
    }
}
