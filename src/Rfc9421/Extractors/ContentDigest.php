<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Extractors;

/**
 * Content digest extract the header request parameter "content-digest"
 */
final  class ContentDigest
{
    private string $regex = '#^(.*?)=:(.*?):$#';

    /*
     *  "content-digest": sha-256=:tFdB/ENGczHMlZMDb66pXoUi2d0OqH2iBHdnN/WV1mc=:
     * //  The format is content-digest => <algorithm>=:<base64 encoded hash>:
     * $digestString = $headers["content-digest"];
     * //  The Base64 encoding may have multiple `=` at the end. So split this at the first `=`.
     * $digestData = explode( separator: "=", string: $digestString, limit: 2 );
     */
    public function __construct(private(set) readonly string $contentDigest)
    {
    }

    /**
     * Hashes are in lowercase, but have a `-` in their fieldName.
     * This is not what hash_algos() expects.
     * $digestAlgorithm = str_replace( search: "-", replace: "", subject: $digestData[0] );
     *
     *
     * @return string sha256
     * @todo check the algorithm fieldName has to be replaced
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
     * @return array<string|null>
     */
    public function getDigestArray(): array
    {
        preg_match($this->regex, $this->contentDigest, $matches);

        return [$matches[1] ?? null, $matches[2] ?? null];
    }

    /**
     * check we have 2 initialContentsParameters after splitting
     */
    public function validateExtraction(): bool
    {
        [$key, $value] = $this->getDigestArray();

        return $this->hasValue($key) && $this->hasValue($value);
    }

    private function hasValue(?string $value): bool
    {
        return null !== $value && '' !== $value;
    }

    /**
     * The hash is surrounded by `:` characters.
     * $digestHash = str_replace( search: ":", replace: "", subject: $digestData[1] );
     * :tFdB/ENGczHMlZMDb66pXoUi2d0OqH2iBHdnN/WV1mc=:
     *
     * @return string|null tFdB/ENGczHMlZMDb66pXoUi2d0OqH2iBHdnN/WV1mc= without the colons on start and the ending
     */
    public function getRealBase64EncodedValue(): ?string
    {
        return $this->getExplodedHash();
    }

    public function getExplodedHash(): null|string
    {
        return $this->getDigestArray()[1];
    }
}
