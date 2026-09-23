<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Verify;

use Filefabrik\Hsap\Exceptions\VerifyHeaderSignatureException;
use Filefabrik\Hsap\Extractors\Components;
use Filefabrik\Hsap\Extractors\Metadata;
use Filefabrik\Hsap\Support\SignaturInput;
use InvalidArgumentException;

/**
 * Holds the decoded
 */
final class HeaderSignatureInput
{
    public private(set) null|SignaturInput $signatureInput = null;
    /**
     * only if extraction was successfully
     *
     * @var array|null
     */
    public private(set) null|array $extractedComponents = [];
    /**
     * only if extraction was successfully
     *
     * @var Metadata|null
     */
    public private(set) null|Metadata $extractedMetadata = null;

    public function __construct(
        public private(set) readonly string $headerSignatureInputValue,
        public private(set) readonly string $signatureNameKey,
    ) {
    }

    /**
     * Central decoding action
     *
     * @throws VerifyHeaderSignatureException
     */
    public function extract(): void
    {
        // in future todo, if set keyname look out for the correct line
        $raw = $this->extractSignatureString();

        $this->signatureInput = SignaturInput::extract($raw);

        if (!$this->signatureInput->components) {
            throw new InvalidArgumentException('missing components');
        }

        $this->extractedComponents = new Components($this->signatureInput->components)->toArray();

        if ($this->signatureInput->metadata) {
            $this->extractedMetadata = Metadata::stringToMetadata($this->signatureInput->metadata);
        }
    }

    /**
     * Prepare the Signature-input from request to split the (component) and the metadata
     *
     * @return string
     * @throws VerifyHeaderSignatureException
     */
    private function extractSignatureString(): string
    {
        if ($this->signatureNameKey) {
            if (!str_starts_with($this->headerSignatureInputValue, $this->signatureNameKey)) {
                throw VerifyHeaderSignatureException::invalidSignatureKeyName(
                    $this->signatureNameKey,
                    $this->headerSignatureInputValue,
                );
            }
            $raw = substr($this->headerSignatureInputValue, strlen($this->signatureNameKey));
        } else {
            $raw = $this->headerSignatureInputValue;
        }

        return $raw;
    }

}
