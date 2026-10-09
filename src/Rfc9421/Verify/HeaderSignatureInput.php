<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Verify;

use Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException;
use Filefabrik\Rfc9421\Extractors\Components;
use Filefabrik\Rfc9421\Extractors\Metadata;
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
     *
     * @var array{int?:string} $extractedComponents
     */
    private(set) array $extractedComponents = [];

    /**
     * only if validatedSignatureParts was successfully
     */
    private(set) null|Metadata $extractedMetadata = null;

    public function __construct(
        private(set) readonly string $headerSignatureInputValue,
        private(set) readonly string $signatureNameKey,
    ) {
    }

    /**
     * Central decoding action
     *
     * @throws VerifyHeaderSignatureException|\Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public function extract(): void
    {
        // in future todo, if set keyname look out for the correct line
        $raw = $this->extractSignatureString();

        $this->signatureInput = SignaturInput::extract($raw);

        // empty components
        if (null === $this->signatureInput->components || '' === $this->signatureInput->components) {
            throw new InvalidArgumentException('missing components');
        }

        $this->extractedComponents = new Components($this->signatureInput->components)->toArray();

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

}
