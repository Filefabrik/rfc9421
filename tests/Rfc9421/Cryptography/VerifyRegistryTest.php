<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * todo insert correct copyright
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Cryptography;

use Filefabrik\Rfc9421\Cryptography\VerifyRegistry;
use Filefabrik\Rfc9421\Enums\SigningEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(VerifyRegistry::class)]
final class VerifyRegistryTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public function testRegisterVerifyingMethods(): void
    {
        $this->expectNotToPerformAssertions();
        VerifyRegistry::registerMethods([
            SigningEnum::rsa_pss_sha512->name    =>
                fn (string $signingDatas, $signature, $publicKeyPem): bool => true,
            SigningEnum::rsa_v_1_5_sha_256->name => fn (string $signingDatas, $signature, $publicKeyPem): bool => false,
        ]);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public function testRegisterVerifying(): void
    {
        $this->expectNotToPerformAssertions();
        VerifyRegistry::registerMethod(
            SigningEnum::rsa_pss_sha512->name,
            fn (string $signingDatas, $signature, $publicKeyPem): bool => true,
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public function testVerifying(): void
    {
        VerifyRegistry::registerMethods([
            SigningEnum::rsa_pss_sha512->name    =>
                fn (string $signingDatas, $signature, $publicKeyPem): bool => true,
            SigningEnum::rsa_v_1_5_sha_256->name => fn (string $signingDatas, $signature, $publicKeyPem): bool => false,
        ]);
        self::assertTrue(VerifyRegistry::verifying(SigningEnum::rsa_pss_sha512, '', '', ''));
        self::assertFalse(VerifyRegistry::verifying(SigningEnum::rsa_v_1_5_sha_256, '', '', ''));
    }
}
