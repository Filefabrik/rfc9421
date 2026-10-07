<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Verify;

use Filefabrik\Rfc9421\Exceptions\ContentDigestException;

/**
 * handles the ContentDigest Verify Process
 */
final class VerifyContentDigest
{
    public private(set) ?ContentDigest $contentDigestVerifier = null {
        /**
         * @throws ContentDigestException
         */
        get => $this->contentDigestVerifier ??= $this->prepare();
    }

    public function __construct(
        public private(set) readonly string $contentDigest,
        public private(set) readonly string $body,
    ) {
    }

    /**
     * Re-Hash the Body-Hash from the header
     * * 'content-digest'  => 'sha-512=:PlrIZYU3M76B30wGsL0h6O79BoxHTdAG+RnMPjOyECTSJCN/KnYdOrSCCWjxV3ckkyvdRmZ52//M3WbehCXcPw==:',
     *
     * @throws ContentDigestException
     */
    public function prepare(): ContentDigest
    {
        $contentDigestExtractor = new \Filefabrik\Rfc9421\Extractors\ContentDigest(
            contentDigest: $this->contentDigest,
        );

        if (!$contentDigestExtractor->validateExtraction()) {
            throw ContentDigestException::wrongInputFormat();
        }

        return ContentDigest::fromContentDigestExtractor(
            body                  : $this->body,
            contentDigestExtractor: $contentDigestExtractor,
        );
    }

    /**
     * @return bool true if valid
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     */
    public function verify(): bool
    {
        return $this->contentDigestVerifier->verify();
    }
}
