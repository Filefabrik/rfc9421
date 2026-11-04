<?php
/*
 * Copyright (c) 2026.  rt@filefabrik.com
 * proprietary
 * todo insert correct copyright
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Extractors;

use Filefabrik\Rfc9421\Support\AbstractMetadata;
use Filefabrik\Rfc9421\Support\UnquoteHelper;
use InvalidArgumentException;

/**
 * Metadata decoder from incoming request string
 */
final class Metadata extends AbstractMetadata
{
    public function __construct(private(set) readonly string $metadataString)
    {
    }

    /**
     * for the moment a statical method
     *
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public static function stringToMetadata(string $metadataString): self
    {
        $self = new self($metadataString);
        foreach ($self->extractString() as $key => $value) {
            $self->set($key, $value);
        }

        return $self;
    }

    /**
     * @return array<string,string|int>
     */
    private function extractString(): array
    {
        $segments = [];
        foreach (explode(';', $this->metadataString) as $segment) {
            [$key, $value] = $this->extractPart($segment);
            if (array_key_exists($key, $segments)) {
                throw new InvalidArgumentException(sprintf('Key was already set before segment: %s.', $key));
            }

            $segments[$key] = $this->deQuoteIfNeed($key, $value);
        }

        return $segments;
    }

    /**
     * @return array{string,string}
     */
    private function extractPart(string $segment): array
    {
        $parts = explode('=', $segment);
        if (2 === count($parts)) {
            return [$parts[0], $parts[1]];
        }

        throw new InvalidArgumentException(sprintf('Invalid segment: %s. Expects "key=value"', $segment));
    }

    private function deQuoteIfNeed(string $key, string $value): string|int
    {
        if (in_array($key, self::$propertiesToQuote, true)) {
            $value = UnquoteHelper::unquote($value);
        }

        return $this->toIntegerIfNeed($key, $value);
    }

    private function toIntegerIfNeed(string $key, string|int $value): string|int
    {
        return in_array($key, self::$decodeToInt, true) ? (int)$value : $value;
    }

}
