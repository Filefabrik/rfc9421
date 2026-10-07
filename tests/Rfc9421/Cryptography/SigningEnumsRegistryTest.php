<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Cryptography;

use Filefabrik\Rfc9421\Cryptography\SigningEnum;
use Filefabrik\Rfc9421\Cryptography\SigningEnumsRegistry;
use Filefabrik\Rfc9421Test\Tools\ExtraSigningEnum;
use Filefabrik\Rfc9421Test\Tools\WrongInterfaceEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Filefabrik\Rfc9421\Cryptography\SigningEnumsRegistry::class)]
final class SigningEnumsRegistryTest extends TestCase
{
    public function testFindEnumOrThrow(): void
    {
        $this->expectExceptionObject(
            new \Filefabrik\Rfc9421\Exceptions\SignatureException(
                'The signature enum "throws" for signing is not supported.',
            ),
        );
        SigningEnumsRegistry::findEnumOrThrow('throws');
    }

    public function testFindEnumOrThrowThrows(): void
    {
        $this->assertSame(
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
            new \InvalidArgumentException(
                'The class "'.WrongInterfaceEnum::class.'" does not have the interface "'.\Filefabrik\Rfc9421\Interfaces\SigningEnumInterface::class.'"',
            ),
        );
        SigningEnumsRegistry::addEnumClass(WrongInterfaceEnum::class);
    }

    public function testAddEnumClassThrowNoExists(): void
    {
        $this->expectExceptionObject(
            new \InvalidArgumentException(
                'class "no-class" does not exist',
            ),
        );
        SigningEnumsRegistry::addEnumClass('no-class');
    }
}
