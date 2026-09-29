<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Support;

use Filefabrik\Hsap\Exceptions\SignatureException;
use Filefabrik\Hsap\Interfaces\SigningEnumInterface;

trait EnumSupportTrait
{
    /**
     * @throws SignatureException
     */
    public static function isOwnTypeOrThrow(SigningEnumInterface|string|null $key): SigningEnumInterface
    {
        return self::isOwnType($key) ?? throw SignatureException::signatureEnumNotFound($key);
    }

    public static function isOwnType(self|string|null $key): ?static
    {
        if ($key instanceof self) {
            return $key;
        }

        return array_find(self::cases(), fn($case) => $case->name === $key || $case->value === $key);
    }

    /**
     * Interfaced Method
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Interfaced Method
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }
}
