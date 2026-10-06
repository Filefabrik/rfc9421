<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Cryptography;

use Filefabrik\Rfc9421\Exceptions\SignatureException;
use Filefabrik\Rfc9421\Interfaces\SigningEnumInterface;
use UnexpectedValueException;

/**
 * Holds Verify-Algorithms and executes a Verify Algorithm
 */
final class VerifyRegistry
{
    use SharedRegistryTrait;

    /**
     * Map Verifying Methods
     *
     * @var callable[]
     */
    private static array $stack = [];

    /**
     * @codeCoverageIgnore
     * @noinspection PhpUnused
     */
    private function __construct()
    {
    }

    /**
     * @throws SignatureException
     */
    public static function verifying(
        string|SigningEnumInterface $signingEnum,
        string $data,
        string $signature,
        string $publicKey,
    ): bool {
        $enum = SigningEnum::isOwnTypeOrThrow($signingEnum);

        $method = self::$stack[$enum->getName()] ?? throw new UnexpectedValueException(
            sprintf('Verify method for "%s" not registered.', $enum->getName()),
        );

        return $method($data, $signature, $publicKey);
    }
}
