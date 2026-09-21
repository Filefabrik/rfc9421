<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Verify;

use Filefabrik\Hsap\Signer\SignBody;

/**
 * Validate the content-digest against tempering
 */
final readonly class ContentDigest
{
    public function __construct(
        public private(set) string $body,
        public private(set) string $phpAlgorithm,
        public private(set) string $base64EncodedValue,
    ) {
    }

    public static function fromContentDigestExtractor(
        string $body,
        \Filefabrik\Hsap\Extractors\ContentDigest $contentDigestExtractor,
    ): self {
        return new self(
            body              : $body,
            phpAlgorithm      : $contentDigestExtractor->getPhpAlgorithm(),
            // base 64 encoded
            base64EncodedValue: $contentDigestExtractor->getRealBase64EncodedValue(),
        );
    }

    public function verify(): bool
    {
        $reCalculatedSignature = new SignBody(
            body     : $this->body,
            algorithm: $this->phpAlgorithm,
        )->calculateSignature();

        /**
         * hash must be the same
         */
        return $reCalculatedSignature === $this->base64EncodedValue;
    }
}
