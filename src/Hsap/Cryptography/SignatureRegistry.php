<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Cryptography;

use Closure;
use Filefabrik\Hsap\Exceptions\SignatureException;
use Filefabrik\Hsap\Interfaces\SigningEnumInterface;
use UnexpectedValueException;

/**
 * Holds Signing-Algorithms and executes a SigningAlgorithm
 */
final class SignatureRegistry
{
    /**
     * Map signing Methods
     *
     * @var array
     */
    private static array $signing = [];

    /**
     * @throws SignatureException
     */
    public static function registerSigningMethods(array $signingMethods): void
    {
        foreach ($signingMethods as $key => $signingMethod) {
            self::registerSigning(signingEnumTrait: $key, closure: $signingMethod);
        }
    }

    /**
     * @throws SignatureException
     */
    public static function registerSigning(string|SigningEnum $signingEnumTrait, Closure $closure): void
    {
        self::$signing[SigningEnum::isOwnTypeOrThrow($signingEnumTrait)
                                  ->getName()] = $closure;
    }

    /**
     * @throws SignatureException
     */
    public static function signing(
        string|SigningEnumInterface $signingEnumInterface,
        string $signingDatas,
        string $privateKey,
    ) {
        $enum = SigningEnum::isOwnTypeOrThrow($signingEnumInterface);

        $method = self::$signing[$signingEnumInterface->getName()] ?? throw new UnexpectedValueException(
            'Signing-Method for "'.$signingEnumInterface->getName().'" not registered.',
        );
        // closure
        if ($method instanceof Closure) {
            return $method($signingDatas, $privateKey);
        }

        throw new UnexpectedValueException('signing method for "'.$enum->getName().'" not callable.');
    }

}
