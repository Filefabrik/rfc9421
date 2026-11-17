<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Configuration;

use Filefabrik\Rfc9421\Enums\DigestAlgorithmsEnum;
use Filefabrik\Rfc9421\Exceptions\ContentDigestException;

trait ContentDigestTrait
{
    /**
     * for creating the content digest hash it should sha-256 sha256 sha-512 sha512
     */
    private(set) ?DigestAlgorithmsEnum $contentDigestAlgo = null;

    private bool $mustGenerateContentDigest = false;

    /**
     * Explizit Command that the content-digest has to be rendered from the message body and MUST be set into the HTTP Message Header as ["content-digest" => 'sha-256=:tFdB/ENGczHMlZMDb66pXoUi2d0OqH2iBHdnN/WV1mc=:']
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     */
    public function withContentDigest(string|DigestAlgorithmsEnum $contentDigestAlgo): self
    {
        $this->setcontentDigestAlgo(contentDigestAlgo: $contentDigestAlgo);

        $this->mustGenerateContentDigest = true;

        return $this;
    }

    /**
     * Flag that the "content-digest" will be generated into the HTTP-Header with the value  sha*="...hashed body..."
     * and also automatically adds the "content-digest" into the signed components
     *
     */
    public function isMustGenerateContentDigest(): bool
    {
        return $this->mustGenerateContentDigest;
    }

    /**
     * Hashing the content-digest|body with algo
     * if set, the header line "content-digest" will be created and the hash value will be stored into the header
     *
     */
    public function getContentDigestAlgo(): ?DigestAlgorithmsEnum
    {
        return $this->contentDigestAlgo;
    }

    /**
     * Do not set directly.
     *
     * @throws ContentDigestException
     * @internal
     */
    public function setContentDigestAlgo(null|string|DigestAlgorithmsEnum $contentDigestAlgo): self
    {
        $this->contentDigestAlgo = null === $contentDigestAlgo ?
            null :
            DigestAlgorithmsEnum::isOwnTypeOrThrow($contentDigestAlgo);

        return $this;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     */
    public function getContentDigestHashAlgoOrThrow(): DigestAlgorithmsEnum
    {
        return $this->contentDigestAlgo ?? throw ContentDigestException::hashAlgoNotSupported('Missing');
    }

    public function hasContentDigestHashAlgo(): bool
    {
        return $this->contentDigestAlgo instanceof DigestAlgorithmsEnum;
    }
}
