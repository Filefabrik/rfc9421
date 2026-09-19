<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Signer;

use Filefabrik\Hsap\Support\AbstractMetadata;

/**
 * The Signature-Input header also includes metadata parameters (after the semicolon):
 *
 * @see https://datatracker.ietf.org/doc/html/rfc9421 [RFC 9421, Section 2.3]
 */
final class Metadata extends AbstractMetadata
{

    /**
     * @param array $metadata
     *
     * @return static
     */
    public static function fromArray(array $metadata): self
    {
        $self = new self();
        foreach ($metadata as $key => $value) {
            $self->set($key, $value);
        }

        return $self;
    }
    /**
     * @return string
     */
    public function toString(): string
    {
        $preprocess = [];
        foreach ($this->toArray() as $key => $value) {
            $preprocess[] = $key.'='.(in_array($key, static::$propertiesToQuote) ? '"'.$value.'"' : $value);
        }

        return implode(';', $preprocess);
    }

}
