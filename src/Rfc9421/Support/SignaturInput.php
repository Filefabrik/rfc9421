<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Support;

use Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException;

/**
 * Splitting the 3 segments of a signature-input testValue=("@method" "content-digest");created=1789634264;expires=1789641464;nonce="nonce-now";tag="mastodon" line into
 * $signatureKeyName:   testValue=
 * $components:         "@method" "content-digest"
 * $metadata:           created=1789634264;expires=1789641464;nonce="nonce-now";tag="mastodon"
 * // todo make all getters
 * validate against null if the "segment" found or not
 */
final class SignaturInput
{
    private(set) ?string $signatureKeyName = null;

    private(set) ?string $components = null;

    private(set) ?string $metadata = null;

    private function __construct(private readonly string $signatureLine)
    {
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     */
    public static function extract(string $signatureLine): self
    {
        $instance = new self($signatureLine);

        return $instance->extracting() ?
            $instance :
            throw VerifyHeaderSignatureException::invalidSignatureLine($signatureLine);
    }

    private function extracting(): bool
    {
        $extracted = preg_match('#(.*?)\((.*?)\)(.*?)$#', $this->signatureLine, $matches);

        if (1 === $extracted) {
            $this->signatureKeyName = $matches[1];

            $this->components = $matches[2];

            if (str_starts_with($matches[3], ';')) {
                $this->metadata = substr($matches[3], 1);
            } else {
                $this->metadata = $matches[3];
            }

            return true;
        }

        return false;
    }
}
