<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Support;

trait EnumSupportTrait
{

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
