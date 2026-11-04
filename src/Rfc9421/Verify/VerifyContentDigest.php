<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Verify;

use Filefabrik\Rfc9421\Exceptions\ContentDigestException;

/**
 * handles the ContentDigest Verify Process
 */
final class VerifyContentDigest
{
    private(set) ?ContentDigest $contentDigestVerifier = null;

    public function __construct(
        private(set) readonly string $contentDigest,
        private(set) readonly string $body,
    ) {
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @return bool true if valid
     */
    public function verify(): bool
    {
        return $this->getContentDigestVerifier()
                    ->verify()
        ;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     */
    public function getContentDigestVerifier(): ContentDigest
    {
        return $this->contentDigestVerifier ??= $this->prepare();
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
}
