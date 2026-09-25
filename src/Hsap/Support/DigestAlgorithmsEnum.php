<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Support;

use Filefabrik\Hsap\Exceptions\ContentDigestException;

enum DigestAlgorithmsEnum: string
{
    case sha256 = 'sha-256';
    case sha512 = 'sha-512';

    /**
     * @throws ContentDigestException
     */
    public static function isOwnTypeOrThrow(self|string|null $key): self
    {
        return self::isOwnType($key) ?? throw ContentDigestException::hashAlgoNotSupported($key);
    }

    public static function isOwnType(self|string|null $key): ?self
    {
        if ($key instanceof self) {
            return $key;
        }

        return array_find(self::cases(), fn($case) => $case->name === $key || $case->value === $key);
    }

    /**
     * used in php hash
     *
     * @return string
     */
    public function getPhpAlgorithm(): string
    {
        return $this->name;
    }

    /**
     * used in signature
     *
     * @return string
     */
    public function getDisplayAlgorithm(): string
    {
        return $this->value;
    }

}
