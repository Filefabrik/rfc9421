<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Extractors;

/**
 * Content digest extract the header request parameter "content-digest"
 * @todo verify emptyness
 */
final class ContentDigest
{
    /*
    *  "content-digest": sha-256=:tFdB/ENGczHMlZMDb66pXoUi2d0OqH2iBHdnN/WV1mc=:
    * //  The format is content-digest => <algorithm>=:<base64 encoded hash>:
    * $digestString = $headers["content-digest"];
    * //  The Base64 encoding may have multiple `=` at the end. So split this at the first `=`.
    * $digestData = explode( separator: "=", string: $digestString, limit: 2 );
     */
    public private(set) string $contentDigest;

    /**
     * @param string $contentDigest
     */
    public function __construct(string $contentDigest)
    {
        $this->contentDigest = $contentDigest;
    }

    public function getAlgorithm(): string
    {
        return $this->getDigestArray()[0];
    }

    public function getDigestArray(): array
    {
        return explode(separator: '=', string: $this->contentDigest, limit: 2);
    }

    public function getHash(): string
    {
        return $this->getDigestArray()[1];
    }
}
