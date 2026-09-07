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
         * todo handle internal php and public algo name IANA
         * @var string
         */
        public private(set) string $algorithm,
    ) {
    }

    /**
     * todo modify algorithm for public
     *
     * @return string
     */
    public function getContentDigest(): string
    {
        return $this->algorithm.'=:'.$this->calculateSignature().':';
    }

    public function calculateSignature(): string
    {
        return base64_encode(hash(algo: $this->algorithm, data: $this->body, binary: true));
    }

}
