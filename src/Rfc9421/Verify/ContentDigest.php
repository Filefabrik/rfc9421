<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Verify;

use Filefabrik\Rfc9421\Signer\SignBody;
use Filefabrik\Rfc9421\Support\DigestAlgorithmsEnum;

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
        \Filefabrik\Rfc9421\Extractors\ContentDigest $contentDigestExtractor,
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
            algorithm: DigestAlgorithmsEnum::isOwnTypeOrThrow($this->phpAlgorithm),
        )->calculateSignature();

        /**
         * hash must be the same
         */
        return $reCalculatedSignature === $this->base64EncodedValue;
    }
}
