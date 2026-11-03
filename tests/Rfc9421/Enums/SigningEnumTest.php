<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Enums;

use Filefabrik\Rfc9421\Enums\SigningEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(SigningEnum::class)]
final class SigningEnumTest extends TestCase
{
    public static function dataSigningEnums(): array
    {
        return [
            [SigningEnum::rsa_pss_sha512],
            [SigningEnum::rsa_v_1_5_sha_256],
            [SigningEnum::rsa_v_1_5_sha_384],
            [SigningEnum::rsa_v_1_5_sha_512],
            [SigningEnum::hmac_sha_256],
            [SigningEnum::hmac_sha_384],
            [SigningEnum::hmac_sha_512],
            [SigningEnum::ecdsa_p_256_sha_256],
            [SigningEnum::ecdsa_p_384_sha_384],
            [SigningEnum::ed25519],
            ['rsa_pss_sha512', SigningEnum::rsa_pss_sha512],
            ['rsa_v_1_5_sha_256', SigningEnum::rsa_v_1_5_sha_256],
            ['rsa_v_1_5_sha_384', SigningEnum::rsa_v_1_5_sha_384],
            ['rsa_v_1_5_sha_512', SigningEnum::rsa_v_1_5_sha_512],
            ['hmac_sha_256', SigningEnum::hmac_sha_256],
            ['hmac_sha_384', SigningEnum::hmac_sha_384],
            ['hmac_sha_512', SigningEnum::hmac_sha_512],
            ['ecdsa_p_256_sha_256', SigningEnum::ecdsa_p_256_sha_256],
            ['ecdsa_p_384_sha_384', SigningEnum::ecdsa_p_384_sha_384],
            ['ed25519', SigningEnum::ed25519],

            ['rsa-pss-sha512', SigningEnum::rsa_pss_sha512],
            ['rsa-v1_5-sha256', SigningEnum::rsa_v_1_5_sha_256],
            ['rsa-v1-5-sha384', SigningEnum::rsa_v_1_5_sha_384],
            ['rsa-v1-5-sha512', SigningEnum::rsa_v_1_5_sha_512],
            ['hmac-sha256', SigningEnum::hmac_sha_256],
            ['hmac-sha384', SigningEnum::hmac_sha_384],
            ['hmac-sha512', SigningEnum::hmac_sha_512],
            ['ecdsa-p256-sha256', SigningEnum::ecdsa_p_256_sha_256],
            ['ecdsa-p384-sha384', SigningEnum::ecdsa_p_384_sha_384],
            ['ed25519', SigningEnum::ed25519],

        ];
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    #[DataProvider('dataSigningEnums')]
    public function testIsOwnTypeOrThrow(
        SigningEnum|string $signingEnum,
        ?SigningEnum $exp = null,
    ): void {
        self::assertSame($exp ?? $signingEnum, SigningEnum::isOwnTypeOrThrow($signingEnum));
    }

    #[DataProvider('dataSigningEnums')]
    public function testIsOwnType(
        SigningEnum|string $signingEnum,
        ?SigningEnum $exp = null,
    ): void {
        self::assertSame($exp ?? $signingEnum, SigningEnum::isOwnType($signingEnum));
    }

    public function testGetName(): void
    {
        self::assertSame('ecdsa_p_256_sha_256', SigningEnum::ecdsa_p_256_sha_256->getName());
    }

    public function testGetValue(): void
    {
        self::assertSame('ecdsa-p256-sha256', SigningEnum::ecdsa_p_256_sha_256->getValue());
    }

    public function testCount(): void
    {
        self::assertCount(10, SigningEnum::cases());
    }
}
