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

use Filefabrik\Rfc9421\Cryptography\SignatureRegistry;
use Filefabrik\Rfc9421\Enums\SigningEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

#[CoversClass(SignatureRegistry::class)]
final class SignatureRegistryTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public function testSigning(): void
    {
        SignatureRegistry::registerMethod(
            SigningEnum::ed25519,
            fn (string $signingDatas, string $privateKeyPem): string => 'isSigned',
        );

        self::assertSame('isSigned', SignatureRegistry::signing(SigningEnum::ed25519, '', ''));
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public function testRegisterSigningMethods(): void
    {
        $this->expectNotToPerformAssertions();
        SignatureRegistry::registerMethods(
            [
                SigningEnum::ed25519->name =>
                    fn (string $signingDatas, string $privateKeyPem): string => 'isSigned',
            ],
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public function testRegisterSigning(): void
    {
        $this->expectNotToPerformAssertions();
        SignatureRegistry::registerMethod(
            SigningEnum::ed25519,
            fn (string $signingDatas, string $privateKeyPem): string => 'isSigned',
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public function testSigningNotRegisteredThrow(): void
    {
        SignatureRegistry::clearRegistry();
        $this->expectExceptionObject(
            new UnexpectedValueException('Signing method for "'.SigningEnum::ed25519->name.'" not registered.'),
        );

        SignatureRegistry::signing(SigningEnum::ed25519, '', '');
    }
}
