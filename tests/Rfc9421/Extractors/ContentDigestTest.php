<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Extractors;

use Filefabrik\Rfc9421\Extractors\ContentDigest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContentDigest::class)]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Filefabrik\Rfc9421\Extractors\ContentDigest::class, 'validateExtraction')]
final class ContentDigestTest extends TestCase
{
    public function testvalidateExtractionFalse(): void
    {
        self::assertFalse(new ContentDigest('bullshit')->validateExtraction());

    }

    public function testGetAlgorithm(): void
    {
        $alg = new ContentDigest('sha-256=:tFdB/ENGczHMlZMDb66pXoUi2d0OqH2iBHdnN/WV1mc=:')->getAlgorithm();
        self::assertEquals('sha-256', $alg);
    }

    public function testGetHash(): void
    {
        $hash = new ContentDigest('sha-256=:tFdB/ENGczHMlZMDb66pXoUi2d0OqH2iBHdnN/WV1mc=:')->getExplodedHash();
        self::assertEquals(':tFdB/ENGczHMlZMDb66pXoUi2d0OqH2iBHdnN/WV1mc=:', $hash);
    }

    public function testGetPhpAlgorithm(): void
    {
        $alg = new ContentDigest('sha-256=:tFdB/ENGczHMlZMDb66pXoUi2d0OqH2iBHdnN/WV1mc=:')->getPhpAlgorithm();
        self::assertEquals('sha256', $alg);
    }

    public function testGetRealHashedValue(): void
    {
        $alg = new ContentDigest('sha-256=:tFdB/ENGczHMlZMDb66pXoUi2d0OqH2iBHdnN/WV1mc=:')->getRealBase64EncodedValue();
        self::assertEquals('tFdB/ENGczHMlZMDb66pXoUi2d0OqH2iBHdnN/WV1mc=', $alg);
    }

    public function testGetDigestArray(): void
    {
        $digestArray = new ContentDigest('sha-256=:tFdB/ENGczHMlZMDb66pXoUi2d0OqH2iBHdnN/WV1mc=:')->getDigestArray();
        self::assertEquals(['sha-256', ':tFdB/ENGczHMlZMDb66pXoUi2d0OqH2iBHdnN/WV1mc=:'], $digestArray);
    }

    public function testValidateExtraction(): void
    {
        $validation = new ContentDigest('tFdB/ENGczHMlZMDb66pXoUi2d0OqH2iBHdnN/WV1mc:')->validateExtraction();
        self::assertFalse($validation);
    }
}
