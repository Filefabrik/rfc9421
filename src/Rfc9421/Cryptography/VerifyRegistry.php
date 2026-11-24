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
use Filefabrik\Rfc9421\Exceptions\UnexpectedValue;
use Filefabrik\Rfc9421\Interfaces\SigningEnumInterface;

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
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public static function verifying(
        string|SigningEnumInterface $signingEnumInterface,
        string $data,
        string $signature,
        string $publicKey,
    ): bool {
        $enum = SigningEnum::isOwnTypeOrThrow($signingEnumInterface);

        if (array_key_exists($enum->getName(), self::$stack)) {
            $method = self::$stack[$enum->getName()];

            return $method($data, $signature, $publicKey);
        }
        throw new UnexpectedValue(
            sprintf('Verify method for "%s" not registered.', $enum->getName()),
        );
    }
}
