<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Cryptography;

use Closure;
use Filefabrik\Rfc9421\Enums\SigningEnum;
use Filefabrik\Rfc9421\Exceptions\SignatureException;

trait SharedRegistryTrait
{
    public static function clearRegistry(): void
    {
        self::$stack = [];
    }

    /**
     *
     * @param array<string,Closure> $methods
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
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
