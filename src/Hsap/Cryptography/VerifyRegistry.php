<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Cryptography;

use Closure;
use Filefabrik\Hsap\Exceptions\SignatureException;
use Filefabrik\Hsap\Interfaces\SigningEnumInterface;
use UnexpectedValueException;

/**
 * Holds Verify-Algorithms and executes a Verify Algorithm
 */
final class VerifyRegistry
{
    /**
     * Map Verifying Methods
     *
     * @var array
     */
    private static array $verifying = [];

    /**
     * @throws SignatureException
     */
    public static function registerVerifyingMethods(array $verifyingMethods): void
    {
        foreach ($verifyingMethods as $key => $verifyingMethod) {
            self::registerVerifying(signingEnumTrait: $key, closure: $verifyingMethod);
        }
    }

    /**
     * @throws SignatureException
     */
    public static function registerVerifying(string|SigningEnum $signingEnumTrait, Closure $closure): void
    {
        self::$verifying[SigningEnum::isOwnTypeOrThrow($signingEnumTrait)
                                    ->getName()] = $closure;
    }

    /**
     * @throws SignatureException
     */
    public static function verifying(
        string|SigningEnumInterface $signingEnum,
        string $data,
        string $signature,
        string $publicKey,
    ) {
        $enum = SigningEnum::isOwnTypeOrThrow($signingEnum);

        $method = self::$verifying[$enum->getName()] ?? throw new UnexpectedValueException(
            'Verify-Method for "'.$enum->getName().'" not registered.',
        );

        if ($method instanceof Closure) {
            return $method($data, $signature, $publicKey);
        }

        throw new UnexpectedValueException('verifying method for "'.$enum->getName().'" not callable.');
    }
}
