<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Cryptography;

use Closure;
use Filefabrik\Hsap\Exceptions\SignatureException;

trait SharedRegistryTrait
{
    public static function clearRegistry(): void
    {
        self::$stack = [];
    }

    /**
     * @throws SignatureException
     */
    public static function registerMethods(array $methods): void
    {
        foreach ($methods as $key => $verifyingMethod) {
            self::registerMethod(signingEnumTrait: $key, closure: $verifyingMethod);
        }
    }

    /**
     * @throws SignatureException
     */
    public static function registerMethod(string|SigningEnum $signingEnumTrait, Closure $closure): void
    {
        self::$stack[SigningEnum::isOwnTypeOrThrow($signingEnumTrait)
                                ->getName()] = $closure;
    }
}
