<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Signer;

/**
 * After setting all relevant parameters/body sign the body for header["content-digest"]
 *
 * @todo check algos available
 */
final readonly class SignBody
{
    public function __construct(
        public private(set) string $body,
        /**
         * todo handle internal php and public algo name IANA/RFC
         */
        public private(set) string $algorithm,
    ) {
    }

    /**
     * Getting the Key Value pair for header array
     *
     * @return array
     * @example [$headerKey, $headerValue] = new SignBody($body,'sha-512')->getContentDigestHeaderArray()
     */
    public function getContentDigestHeaderArray(): array
    {
        return ['content-digest', $this->getContentDigest()];
    }

    /**
     * This string will be present in the header "content-digest".
     * The Validator checks the integrity of the body that all parts of the body matches this hash"
     * If the Body was changed outside, the signature does not match and you can be sure, someone has "hacked" the request.
     * todo maybe modify algorithm for public/RFC unique usage
     *
     * @return string
     */
    public function getContentDigest(): string
    {
        return $this->algorithm.'=:'.$this->calculateSignature().':';
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
     * Todo configurable method
     *
     * @return string
     */
    public function hash(): string
    {
        return hash(algo: $this->algorithm, data: $this->body, binary: true);
    }
}
