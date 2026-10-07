<?php

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
     * @param array{string:int|string|null} $metadata
     *
     * @return static
     */
    public static function fromArray(array $metadata): self
    {
        return self::bindMetadata(new self(), $metadata);
    }

    /**
     * @param \Filefabrik\Rfc9421\Signer\Metadata $instance
     * @param array{string:int|string|null}                               $metadata
     *
     * @return self
     */
    public static function bindMetadata(self $instance, array $metadata): self
    {
        foreach ($metadata as $key => $value) {
            $instance->set($key, $value);
        }

        return $instance;
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        $preprocess = [];
        foreach ($this->toArray() as $key => $value) {
            $preprocess[] = $key.'='.(in_array($key, self::$propertiesToQuote, true) ? '"'.$value.'"' : $value);
        }

        return implode(';', $preprocess);
    }

}
