<?php
/*
 * Copyright (c) 2026.  rt@filefabrik.com
 * proprietary
 * todo insert correct copyright
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Cryptography;

use Filefabrik\Rfc9421\Enums\SigningEnum;
use Filefabrik\Rfc9421\Exceptions\SignatureException;
use Filefabrik\Rfc9421\Interfaces\SigningEnumInterface;
use UnexpectedValueException;

/**
 * Holds Signing-Algorithms and executes a SigningAlgorithm
 */
final class SignatureRegistry
{
    use SharedRegistryTrait;

    /**
     * Map signing Methods
     *
     * @var array<callable>
     */
    private static array $stack = [];

    /**
     * only static class
     *
     * @codeCoverageIgnore
     * @noinspection PhpUnused
     */
    private function __construct()
    {
    }

    /**
     * @throws SignatureException
     */
    public static function signing(
        string|SigningEnumInterface $signingEnumInterface,
        string $signingDatas,
        string $privateKey,
    ): string {
        $enum = SigningEnum::isOwnTypeOrThrow($signingEnumInterface);

        $method = self::$stack[$enum->getName()] ?? throw new UnexpectedValueException(
            sprintf('Signing method for "%s" not registered.', $enum->getName()),
        );

        // closure
        return $method($signingDatas, $privateKey);
    }

}
