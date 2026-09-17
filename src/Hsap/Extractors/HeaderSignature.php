<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Extractors;

use Filefabrik\Hsap\Exceptions\SignatureExtractionException;

final class HeaderSignature
{
    public private(set) array|null|SignatureExtractionException $extraction = null {
        get => $this->extraction ??= $this->getValidatedSignatureParts();
    }

    public function __construct(public private(set) readonly string $signatureString)
    {
    }

    /**
     *
     * @return string|null null on error
     */
    public function extractSignatureNameKey(): ?string
    {
        if ($extracted = $this->getValidExtraction()) {
            return $extracted[1];
        }

        // invalid
        return null;
    }

    public function getValidExtraction(): null|array
    {
        return $this->extraction instanceof SignatureExtractionException ? null : $this->extraction;
    }

    public function isValid(): bool
    {
        return null !== $this->getValidExtraction();
    }

    /**
     *
     * @return string|null null on not found
     */
    public function getSignatureValue(): ?string
    {
        if ($extracted = $this->getValidExtraction()) {
            return $extracted[2];
        }

        // invalid
        return null;
    }

    /**
     * checks we have the :crypted-code: ...between the double colons
     *
     * @return array|SignatureExtractionException SignatureExtractionException on format missmatch
     *
     */
    public function getValidatedSignatureParts(): SignatureExtractionException|array
    {
        $extracted = $this->extractSignatureString();
        $cnt       = count($extracted);
        if (3 === $cnt) {
            return $extracted;
        }

        return new SignatureExtractionException('');
    }

    public function extractSignatureString(): array
    {
        preg_match('#^(.*?):(.*?):#', $this->signatureString, $matches);

        return $matches;
    }
}
