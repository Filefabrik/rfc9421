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

use Filefabrik\Rfc9421\Exceptions\SignatureExtractionException;
use Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException;
use Filefabrik\Rfc9421\Extractors\Components;
use Filefabrik\Rfc9421\Extractors\Metadata;
use Filefabrik\Rfc9421\SigningComponents;
use Filefabrik\Rfc9421\Support\SignaturInput;
use InvalidArgumentException;

/**
 * Holds the decoded
 */
final class HeaderSignatureInput
{
    private(set) null|SignaturInput $signatureInput = null;

    /**
     * only if validatedSignatureParts was successfully
     */
    private(set) null|Metadata $extractedMetadata = null;

    /**
     * only if validatedSignatureParts was successfully
     */
    private ?SigningComponents $extractedComponents = null;

    public function __construct(
        private(set) readonly string $headerSignatureInputValue,
        /**
         * if the signatureNameKey not set/null, then detect it during the extract
         *
         */
        private(set) readonly string $signatureNameKey,
    ) {
    }

    /**
     * Central decoding action
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     */
    public function extract(): void
    {
        // in future todo, if set keyname look out for the correct line
        $raw = $this->extractSignatureString();

        $this->signatureInput = SignaturInput::extract($raw);

        // empty components
        if (null === $this->signatureInput->components || '' === $this->signatureInput->components) {
            // todo more descriptive that we can not sign if we do not have components
            throw new InvalidArgumentException('missing components');
        }

        $this->extractedComponents = new Components(componentsString: $this->signatureInput->components)
            ->extractToComponentsObject()
        ;

        if (null !== $this->signatureInput->metadata) {
            $this->extractedMetadata = Metadata::stringToMetadata($this->signatureInput->metadata);
        }
    }

    /**
     * Prepare the Signature-input from request to split the (component) and the metadata
     *
     * @throws VerifyHeaderSignatureException
     */
    private function extractSignatureString(): string
    {
        if ('' !== $this->signatureNameKey) {
            if (!str_starts_with($this->headerSignatureInputValue, $this->signatureNameKey)) {
                throw VerifyHeaderSignatureException::invalidSignatureKeyName(
                    $this->signatureNameKey,
                    $this->headerSignatureInputValue,
                );
            }

            return substr($this->headerSignatureInputValue, strlen($this->signatureNameKey));
        }

        return $this->headerSignatureInputValue;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function getExtractedComponents(): SigningComponents
    {
        if ($this->extractedComponents instanceof SigningComponents) {
            return $this->extractedComponents;
        }

        throw SignatureExtractionException::notProceeded();
    }

}
