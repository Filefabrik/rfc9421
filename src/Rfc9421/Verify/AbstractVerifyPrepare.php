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

use Filefabrik\Rfc9421\Exceptions\UnexpectedValue;
use Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException;
use Filefabrik\Rfc9421\Extractors\ContentDigest;
use Filefabrik\Rfc9421\Extractors\HeaderSignature;
use Filefabrik\Rfc9421\Signer\Metadata;
use Filefabrik\Rfc9421\SigningConfiguration;

/**
 * Todo check shared methods
 */
abstract class AbstractVerifyPrepare
{
    private(set) ?HeaderSignature $headerSignatureExtractor = null;

    protected ?HeaderSignatureInput $signatureInputInstance = null;

    protected HeaderHelper $headerHelper;

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
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
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
            instance: $this->signingConfiguration->getMetadata(),
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
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     * @return array{}|array{created?: int, expires?: int, nonce?: string, keyid?: string, alg?: string, tag?: string}
     */
    protected function getExtractedMetadataArray(): array
    {
        return $this->getSignaturInputInstance()->extractedMetadata?->toArray() ?? throw new UnexpectedValue(
            'No Metadata Found',
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     */
    protected function getSignaturInputInstance(): HeaderSignatureInput
    {
        $headerSignatureInstance = new HeaderSignatureInput(
            headerSignatureInputValue: $this->headerHelper
                ->getSignatureInputLine(),
            signatureNameKey         : $this->signingConfiguration->getSignatureKeyName(),
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
        $hasContentDigestLine = $this->headerHelper
            ->hasContentDigestLine()
        ;
        if ($hasContentDigestLine) {
            $this->getSigningConfiguration()
                 ->setContentDigestAlgo(
                     contentDigestAlgo: new ContentDigest(
                         contentDigest: $this->headerHelper
                             ->getContentDigestLine(),
                     )->getAlgorithm(),
                 )
            ;
        }
    }

    private function getSigningConfiguration(): SigningConfiguration
    {
        return $this->signingConfiguration;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     */
    public function getSignatureInputInstance(): HeaderSignatureInput
    {
        return $this->signatureInputInstance ?? throw new VerifyHeaderSignatureException('missing header signature');
    }
}
