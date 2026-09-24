<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Verify;

use Filefabrik\Hsap\Exceptions\ContentDigestException;

/**
 * handles the ContentDigest Verify Process
 * Todo separate testing
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
     * * 'content-digest'  => 'sha512=:PlrIZYU3M76B30wGsL0h6O79BoxHTdAG+RnMPjOyECTSJCN/KnYdOrSCCWjxV3ckkyvdRmZ52//M3WbehCXcPw==:',
     *
     * @throws ContentDigestException
     */
    public function prepare(): ContentDigest
    {
        $contentDigestExtractor = new \Filefabrik\Hsap\Extractors\ContentDigest(
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
     */
    public function verify(): bool
    {
        return $this->contentDigestVerifier->verify();
    }
}
