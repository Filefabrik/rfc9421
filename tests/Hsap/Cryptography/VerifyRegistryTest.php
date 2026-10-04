<?php

declare(strict_types=1);

namespace Filefabrik\HsapTest\Cryptography;

use Filefabrik\Hsap\Cryptography\SigningEnum;
use Filefabrik\Hsap\Cryptography\VerifyRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Filefabrik\Hsap\Cryptography\VerifyRegistry::class)]
final class VerifyRegistryTest extends TestCase
{
    public function testRegisterVerifyingMethods(): void
    {
        $this->expectNotToPerformAssertions();
        VerifyRegistry::registerMethods([
            SigningEnum::rsa_pss_sha512->name    =>
                function(string $signingDatas, $signature, $publicKeyPem): bool {
                    return true;
                },
            SigningEnum::rsa_v_1_5_sha_256->name => function(string $signingDatas, $signature, $publicKeyPem): bool {
                return false;
            },
        ]);
    }

    public function testRegisterVerifying(): void
    {
        $this->expectNotToPerformAssertions();
        VerifyRegistry::registerMethod(
            SigningEnum::rsa_pss_sha512->name,
            function(string $signingDatas, $signature, $publicKeyPem): bool {
                return true;
            },
        );
    }

    public function testVerifying(): void
    {
        VerifyRegistry::registerMethods([
            SigningEnum::rsa_pss_sha512->name    =>
                function(string $signingDatas, $signature, $publicKeyPem): bool {
                    return true;
                },
            SigningEnum::rsa_v_1_5_sha_256->name => function(string $signingDatas, $signature, $publicKeyPem): bool {
                return false;
            },
        ]);
        $this->assertTrue(VerifyRegistry::verifying(SigningEnum::rsa_pss_sha512, '', '', ''));
        $this->assertFalse(VerifyRegistry::verifying(SigningEnum::rsa_v_1_5_sha_256, '', '', ''));
    }
}
