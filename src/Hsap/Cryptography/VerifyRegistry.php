<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Cryptography;

use Filefabrik\Hsap\Exceptions\SignatureException;
use Filefabrik\Hsap\Interfaces\SigningEnumInterface;
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
     * @var array
     */
    private static array $stack = [];

    /**
     * @codeCoverageIgnore
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
    ) {
        $enum = SigningEnum::isOwnTypeOrThrow($signingEnum);

        $method = self::$stack[$enum->getName()] ?? throw new UnexpectedValueException(
            sprintf('Verify method for "%s" not registered.', $enum->getName()),
        );

        return $method($data, $signature, $publicKey);
    }
}
