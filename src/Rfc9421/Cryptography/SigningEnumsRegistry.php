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

use Filefabrik\Rfc9421\Enums\SigningEnum;
use Filefabrik\Rfc9421\Exceptions\SignatureException;
use Filefabrik\Rfc9421\Interfaces\SigningEnumInterface;
use InvalidArgumentException;

/**
 * During receive a signature input with perhaps own not known signing algo you are able to use also or exclusively your own naming
 */
final class SigningEnumsRegistry
{
    /**
     * @var array|class-string[]
     */
    private static array $enumClasses = [SigningEnum::class];

    public static function addEnumClass(string $enumClass): void
    {
        if (class_exists($enumClass)) {
            $ifClass = SigningEnumInterface::class;
            $impl    = class_implements(object_or_class: $enumClass);
            if (in_array($ifClass, $impl, true)) {
                self::$enumClasses[] = $enumClass;

                return;
            }

            throw new InvalidArgumentException(
                sprintf('The class "%s" does not have the interface "%s"', $enumClass, $ifClass),
            );
        }

        throw new InvalidArgumentException(sprintf('class "%s" does not exist', $enumClass));
    }

    /**
     * @throws SignatureException
     */
    public static function findEnumOrThrow(string|SigningEnumInterface $enum): SigningEnumInterface
    {
        foreach (self::$enumClasses as $enumClass) {
            $fEnum = $enumClass::isOwnType($enum);
            if ($fEnum) {
                return $fEnum;
            }
        }

        throw SignatureException::signatureEnumNotFound(is_string($enum) ? $enum : $enum->getName());
    }
}
