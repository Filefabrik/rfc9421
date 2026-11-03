<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signer;

use Filefabrik\Rfc9421\Enums\DigestAlgorithmsEnum;
use Filefabrik\Rfc9421\Enums\HeaderKeysEnum;

/**
 * After setting all relevant parameters/body sign the body for header["content-digest"]
 */
final readonly class SignBody
{
    private DigestAlgorithmsEnum $algorithm;

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     */
    public function __construct(
        private(set) string $body,
        DigestAlgorithmsEnum|string $algorithm,
    ) {
        $this->algorithm = DigestAlgorithmsEnum::isOwnTypeOrThrow($algorithm);
    }

    /**
     * Getting the Key Value pair for header array
     *
     * @return array{string,string}
     * @example [$headerKey, $headerValue] = new SignBody($body,'sha512')->getContentDigestHeaderArray()
     */
    public function getContentDigestHeaderArray(): array
    {
        return [HeaderKeysEnum::content_digest->value, $this->getContentDigest()];
    }

    /**
     * This string will be present in the header "content-digest".
     * The Validator checks the integrity of the body that all parts of the body matches this hash"
     * If the Body was changed outside, the signature does not match, and you can be sure, someone has "hacked" the request.
     *

     */
    public function getContentDigest(): string
    {
        return $this->algorithm
                ->getDisplayAlgorithm().'=:'.$this->calculateSignature().':';
    }

    /**
     * Calculate the whole message body and encode with base64 for proper chars
     */
    public function calculateSignature(): string
    {
        return base64_encode($this->hash());
    }

    /**
     * Create the hash
     */
    public function hash(): string
    {
        return hash(
            algo  : $this->algorithm
                ->getPhpAlgorithm(),
            data  : $this->body,
            binary: true,
        );
    }
}
