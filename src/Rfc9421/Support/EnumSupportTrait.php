<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Support;

trait EnumSupportTrait
{
    public static function isOwnType(self|string|null $key): ?static
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
