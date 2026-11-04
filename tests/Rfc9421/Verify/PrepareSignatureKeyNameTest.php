<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Verify;

use Filefabrik\Rfc9421\Extractors\HeaderSignature;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421\Verify\PrepareSignatureKeyName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PrepareSignatureKeyName::class)]
final class PrepareSignatureKeyNameTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     */
    public function testPrepare(): void
    {
        $sc  = new SigningConfiguration();
        $hse = new HeaderSignature('testValue=:Gemgf5YIl0pbTUuU5gqaI+U4Br6SRCuODMwPFpHN08gPVqFQ67m0hBFmZu81s:');

        $instance = new PrepareSignatureKeyName(
            signingConfiguration    : $sc,
            headerSignatureExtractor: $hse,
        );

        $instance->prepare();

        self::assertSame('testValue=', $sc->signatureKeyName);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     */
    public function testPrepareWithoutSignatureKeyExtraction(): void
    {
        $sc  = new SigningConfiguration();
        $hse = new HeaderSignature('blalba=:Gemgf5YIl0pbTUuU5gqaI+U4Br6SRCuODMwPFpHN08gPVqFQ67m0hBFmZu81s:');
        $sc->setSignatureKeyName('katze=');
        $instance = new PrepareSignatureKeyName(
            signingConfiguration    : $sc,
            headerSignatureExtractor: $hse,
        );

        $instance->prepare();

        self::assertSame('katze=', $sc->signatureKeyName);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     */
    public function testPrepareThrow(): void
    {
        $sc  = new SigningConfiguration();
        $hse = new HeaderSignature(':Gemgf5YIl0pbTUuU5gqaI+U4Br6SRCuODMwPFpHN08gPVqFQ67m0hBFmZu81s:');

        $instance = new PrepareSignatureKeyName(
            signingConfiguration    : $sc,
            headerSignatureExtractor: $hse,
        );

        $instance->prepare();

        self::assertSame('', $sc->signatureKeyName);
    }
}
