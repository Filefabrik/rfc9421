<?php

declare(strict_types=1);

namespace Filefabrik\HsapTest\Verify;

use Filefabrik\Hsap\Extractors\HeaderSignature;
use Filefabrik\Hsap\SigningConfiguration;
use Filefabrik\Hsap\Verify\PrepareSignatureKeyName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PrepareSignatureKeyName::class)]
final class PrepareSignatureKeyNameTest extends TestCase
{
    public function testPrepare(): void
    {
        $sc  = new SigningConfiguration();
        $hse = new HeaderSignature('testValue=:Gemgf5YIl0pbTUuU5gqaI+U4Br6SRCuODMwPFpHN08gPVqFQ67m0hBFmZu81s:');

        $instance = new PrepareSignatureKeyName(
            signingConfiguration    : $sc,
            headerSignatureExtractor: $hse,
        );

        $instance->prepare();

        $this->assertSame('testValue=', $sc->signatureKeyName);
    }

    public function testPrepareWithoutSignatureKeyExtraction(): void
    {
        $sc  = new SigningConfiguration();
        $hse = new HeaderSignature('blalba=:Gemgf5YIl0pbTUuU5gqaI+U4Br6SRCuODMwPFpHN08gPVqFQ67m0hBFmZu81s:');
        $sc->withSignatureKeyName('katze=');
        $instance = new PrepareSignatureKeyName(
            signingConfiguration    : $sc,
            headerSignatureExtractor: $hse,
        );

        $instance->prepare();

        $this->assertSame('katze=', $sc->signatureKeyName);
    }  public function testPrepareThrow(): void
{
    $sc  = new SigningConfiguration();
    $hse = new HeaderSignature(':Gemgf5YIl0pbTUuU5gqaI+U4Br6SRCuODMwPFpHN08gPVqFQ67m0hBFmZu81s:');

    $instance = new PrepareSignatureKeyName(
        signingConfiguration    : $sc,
        headerSignatureExtractor: $hse,
    );

    $instance->prepare();

    $this->assertSame('', $sc->signatureKeyName);
}
}
