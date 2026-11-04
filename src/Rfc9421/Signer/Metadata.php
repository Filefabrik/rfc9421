<?php
/*
 * Copyright (c) 2026.  rt@filefabrik.com
 * proprietary
 * todo insert correct copyright
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signer;

use Filefabrik\Rfc9421\Support\AbstractMetadata;

/**
 * The Signature-Input header also includes metadata parameters (after the semicolon):
 *
 * @see https://datatracker.ietf.org/doc/html/rfc9421 [RFC 9421, Section 2.3]
 */
final class Metadata extends AbstractMetadata
{
    /**
     * @param array<string,int|string> $metadata
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public static function fromArray(array $metadata): self
    {
        return self::bindMetadata(new self(), $metadata);
    }

    /**
     * @param array{}|array{created?: int, expires?: int, nonce?: string, keyid?: string, alg?: string, tag?: string} $metadata
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public static function bindMetadata(self $instance, array $metadata): self
    {
        foreach ($metadata as $key => $value) {
            $instance->set($key, $value);
        }

        return $instance;
    }

    public function toString(): string
    {
        $preprocess = [];
        foreach ($this->toArray() as $key => $value) {
            $preprocess[] = $key.'='.(in_array($key, self::$propertiesToQuote, true) ? '"'.$value.'"' : $value);
        }

        return implode(';', $preprocess);
    }

}
