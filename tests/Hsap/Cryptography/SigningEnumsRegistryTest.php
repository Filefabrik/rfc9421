<?php

declare(strict_types=1);

namespace Filefabrik\HsapTest\Cryptography;

use Filefabrik\Hsap\Cryptography\SigningEnum;
use Filefabrik\Hsap\Cryptography\SigningEnumsRegistry;
use Filefabrik\HsapTest\Tools\ExtraSigningEnum;
use Filefabrik\HsapTest\Tools\WrongInterfaceEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Filefabrik\Hsap\Cryptography\SigningEnumsRegistry::class)]
final class SigningEnumsRegistryTest extends TestCase
{
    public function testFindEnumOrThrow(): void
    {
        $this->expectExceptionObject(
            new \Filefabrik\Hsap\Exceptions\SignatureException(
                'The signature enum "throws" for signing is not supported.',
            ),
        );
        SigningEnumsRegistry::findEnumOrThrow('throws');
    }

    public function testFindEnumOrThrowThrows(): void
    {
        $this->assertSame(
            SigningEnum::ecdsa_p_512_sha_512,
            SigningEnumsRegistry::findEnumOrThrow(SigningEnum::ecdsa_p_512_sha_512),
        );
    }

    public function testAddEnumClass(): void
    {
        $this->expectNotToPerformAssertions();
        SigningEnumsRegistry::addEnumClass(ExtraSigningEnum::class);
    }

    public function testAddEnumClassThrowInterface()
    {
        $this->expectExceptionObject(
            new \InvalidArgumentException(
                'The class "'.WrongInterfaceEnum::class.'" does not have the interface "'.\Filefabrik\Hsap\Interfaces\SigningEnumInterface::class.'"',
            ),
        );
        SigningEnumsRegistry::addEnumClass(WrongInterfaceEnum::class);
    }

    public function testAddEnumClassThrowNoExists()
    {
        $this->expectExceptionObject(
            new \InvalidArgumentException(
                'class "no-class" does not exist',
            ),
        );
        SigningEnumsRegistry::addEnumClass('no-class');
    }
}
