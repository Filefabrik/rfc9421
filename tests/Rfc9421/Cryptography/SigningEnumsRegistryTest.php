<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Cryptography;

use Filefabrik\Rfc9421\Cryptography\SigningEnumsRegistry;
use Filefabrik\Rfc9421\Enums\SigningEnum;
use Filefabrik\Rfc9421\Exceptions\SignatureException;
use Filefabrik\Rfc9421\Interfaces\SigningEnumInterface;
use Filefabrik\Rfc9421Test\Tools\ExtraSigningEnum;
use Filefabrik\Rfc9421Test\Tools\WrongInterfaceEnum;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SigningEnumsRegistry::class)]
final class SigningEnumsRegistryTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public function testFindEnumOrThrow(): void
    {
        $this->expectExceptionObject(
            new SignatureException(
                'The signature enum "throws" for signing is not supported.',
            ),
        );
        SigningEnumsRegistry::findEnumOrThrow('throws');
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public function testFindEnumOrThrowThrows(): void
    {
        self::assertSame(
            SigningEnum::ecdsa_p_384_sha_384,
            SigningEnumsRegistry::findEnumOrThrow(SigningEnum::ecdsa_p_384_sha_384),
        );
    }

    public function testAddEnumClass(): void
    {
        $this->expectNotToPerformAssertions();
        SigningEnumsRegistry::addEnumClass(ExtraSigningEnum::class);
    }

    public function testAddEnumClassThrowInterface(): void
    {
        $this->expectExceptionObject(
            new InvalidArgumentException(
                'The class "'.WrongInterfaceEnum::class.'" does not have the interface "'.SigningEnumInterface::class.'"',
            ),
        );
        SigningEnumsRegistry::addEnumClass(WrongInterfaceEnum::class);
    }

    public function testAddEnumClassThrowNoExists(): void
    {
        $this->expectExceptionObject(
            new InvalidArgumentException(
                'class "no-class" does not exist',
            ),
        );
        SigningEnumsRegistry::addEnumClass('no-class');
    }
}
