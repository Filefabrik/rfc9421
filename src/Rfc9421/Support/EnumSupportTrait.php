<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Support;

use Filefabrik\Rfc9421\Interfaces\SigningEnumInterface;

trait EnumSupportTrait
{
    public static function isOwnType(self|SigningEnumInterface|string|null $key): ?static
    {
        if ($key instanceof self) {
            return $key;
        }

        return array_find(self::cases(), fn($case): bool => $case->name === $key || $case->value === $key);
    }

    /**
     * Interfaced Method
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Interfaced Method
     */
    public function getValue(): string
    {
        return $this->value;
    }
}
