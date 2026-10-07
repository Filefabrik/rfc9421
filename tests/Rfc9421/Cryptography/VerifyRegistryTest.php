<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Cryptography;

use Filefabrik\Rfc9421\Cryptography\SigningEnum;
use Filefabrik\Rfc9421\Cryptography\VerifyRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Filefabrik\Rfc9421\Cryptography\VerifyRegistry::class)]
final class VerifyRegistryTest extends TestCase
{
    public function testRegisterVerifyingMethods(): void
    {
        $this->expectNotToPerformAssertions();
        VerifyRegistry::registerMethods([
            SigningEnum::rsa_pss_sha512->name    =>
                fn(string $signingDatas, $signature, $publicKeyPem): bool => true,
            SigningEnum::rsa_v_1_5_sha_256->name => fn(string $signingDatas, $signature, $publicKeyPem): bool => false,
        ]);
    }

    public function testRegisterVerifying(): void
    {
        $this->expectNotToPerformAssertions();
        VerifyRegistry::registerMethod(
            SigningEnum::rsa_pss_sha512->name,
            fn(string $signingDatas, $signature, $publicKeyPem): bool => true,
        );
    }

    public function testVerifying(): void
    {
        VerifyRegistry::registerMethods([
            SigningEnum::rsa_pss_sha512->name    =>
                fn(string $signingDatas, $signature, $publicKeyPem): bool => true,
            SigningEnum::rsa_v_1_5_sha_256->name => fn(string $signingDatas, $signature, $publicKeyPem): bool => false,
        ]);
        $this->assertTrue(VerifyRegistry::verifying(SigningEnum::rsa_pss_sha512, '', '', ''));
        $this->assertFalse(VerifyRegistry::verifying(SigningEnum::rsa_v_1_5_sha_256, '', '', ''));
    }
}
