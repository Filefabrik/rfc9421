<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signer;

use Filefabrik\Rfc9421\Exceptions\ContentDigestException;
use Filefabrik\Rfc9421\Support\DigestAlgorithmsEnum;

/**
 * After setting all relevant parameters/body sign the body for header["content-digest"]
 */
final class SignBody
{
    public function __construct(
        public private(set) readonly string $body,
        public private(set) DigestAlgorithmsEnum|string $algorithm {
            /**
             * @throws ContentDigestException
             */
            set => $this->algorithm = DigestAlgorithmsEnum::isOwnTypeOrThrow($value);
        },
    ) {
    }

    /**
     * Getting the Key Value pair for header array
     *
     * @return array
     * @example [$headerKey, $headerValue] = new SignBody($body,'sha512')->getContentDigestHeaderArray()
     */
    public function getContentDigestHeaderArray(): array
    {
        return ['content-digest', $this->getContentDigest()];
    }

    /**
     * This string will be present in the header "content-digest".
     * The Validator checks the integrity of the body that all parts of the body matches this hash"
     * If the Body was changed outside, the signature does not match and you can be sure, someone has "hacked" the request.
     *
     * @return string
     */
    public function getContentDigest(): string
    {
        return $this->algorithm->getDisplayAlgorithm().'=:'.$this->calculateSignature().':';
    }

    /**
     * Calculate the whole message body and encode with base64 for proper chars
     *
     * @return string
     */
    public function calculateSignature(): string
    {
        return base64_encode($this->hash());
    }

    /**
     * Create the hash
     *
     * @return string
     */
    public function hash(): string
    {
        return hash(algo: $this->algorithm->getPhpAlgorithm(), data: $this->body, binary: true);
    }
}
