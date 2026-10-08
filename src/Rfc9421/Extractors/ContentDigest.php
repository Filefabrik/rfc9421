<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Extractors;

/**
 * Content digest extract the header request parameter "content-digest"
 *
 * @todo verify emptyness
 */
final readonly class ContentDigest
{
    /*
     *  "content-digest": sha-256=:tFdB/ENGczHMlZMDb66pXoUi2d0OqH2iBHdnN/WV1mc=:
     * //  The format is content-digest => <algorithm>=:<base64 encoded hash>:
     * $digestString = $headers["content-digest"];
     * //  The Base64 encoding may have multiple `=` at the end. So split this at the first `=`.
     * $digestData = explode( separator: "=", string: $digestString, limit: 2 );
     */
    public function __construct(private(set) string $contentDigest)
    {
    }

    /**
     * Hashes are in lowercase, but have a `-` in their name.
     * This is not what hash_algos() expects.
     * $digestAlgorithm = str_replace( search: "-", replace: "", subject: $digestData[0] );
     *
     *
     * @return string sha256
     * @todo check the algorithm name has to be replaced
     */
    public function getPhpAlgorithm(): string
    {
        return str_replace(search: '-', replace: '', subject: $this->getAlgorithm());
    }

    /**
     * @return string sha-256
     */
    public function getAlgorithm(): string
    {
        return $this->getDigestArray()[0];
    }

    /**
     * todo ...shiddy extractor
     *
     * @return array<string>
     */
    public function getDigestArray(): array
    {
        return explode(separator: '=', string: $this->contentDigest, limit: 2);
    }

    /**
     * check we have 2 parts after splitting
     * todo more clever validatedSignatureParts check
     */
    public function validateExtraction(): bool
    {
        return (2 === count($this->getDigestArray()));
    }

    /**
     * The hash is surrounded by `:` characters.
     * $digestHash = str_replace( search: ":", replace: "", subject: $digestData[1] );
     * :tFdB/ENGczHMlZMDb66pXoUi2d0OqH2iBHdnN/WV1mc=:
     *
     * @return string tFdB/ENGczHMlZMDb66pXoUi2d0OqH2iBHdnN/WV1mc= without the colons on start and the ending
     */
    public function getRealBase64EncodedValue(): string
    {
        return str_replace(search: ':', replace: '', subject: $this->getExplodedHash());
    }

    public function getExplodedHash(): string
    {
        return $this->getDigestArray()[1];
    }
}
