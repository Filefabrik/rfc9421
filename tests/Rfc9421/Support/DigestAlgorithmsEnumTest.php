<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Support;

use Filefabrik\Rfc9421\Exceptions\ContentDigestException;
use Filefabrik\Rfc9421\Support\DigestAlgorithmsEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(DigestAlgorithmsEnum::class)]
final class DigestAlgorithmsEnumTest extends TestCase
{
    /**
     * @return array[]
     */
    public static function validDigestAlgorithms(): array
    {
        return [
            ['sha256', DigestAlgorithmsEnum::sha256],
            ['sha-256', DigestAlgorithmsEnum::sha256],
            ['sha512', DigestAlgorithmsEnum::sha512],
            ['sha-512', DigestAlgorithmsEnum::sha512,],
            [DigestAlgorithmsEnum::sha512, DigestAlgorithmsEnum::sha512,],
        ];
    }

    public static function invalidDigestAlgorithms(): array
    {
        return [
            [null],
            ['sha-256s'],
        ];
    }

    /**
     * @throws ContentDigestException
     */
    #[DataProvider('validDigestAlgorithms')]
    public function testIsOwnTypeOrThrow(...$datas): void
    {
        $this->assertSame(
            $datas[1],
            DigestAlgorithmsEnum::isOwnTypeOrThrow($datas[0]),
        );
    }

    /**
     * @throws ContentDigestException
     */
    public function testIsOwnTypeOrThrowOnNull(): void
    {
        $this->expectExceptionObject(
            new ContentDigestException('Hash algorithm "" for the content-digest not supported.'),
        );
        DigestAlgorithmsEnum::isOwnTypeOrThrow(null);
    }

    /**
     * @throws ContentDigestException
     */
    public function testIsOwnTypeOrThrowOnNotKnownString(): void
    {
        $this->expectExceptionObject(
            new ContentDigestException('Hash algorithm "never mind" for the content-digest not supported.'),
        );
        DigestAlgorithmsEnum::isOwnTypeOrThrow('never mind');
    }

    /**
     */
    #[DataProvider('validDigestAlgorithms')]
    public function testIsOwnType(...$datas): void
    {
        $this->assertSame(
            $datas[1],
            DigestAlgorithmsEnum::isOwnType($datas[0]),
        );
    }

    /**
     */
    #[DataProvider('invalidDigestAlgorithms')]
    public function testIsOwnTypeNull(...$datas): void
    {
        $this->assertSame(
            null,
            DigestAlgorithmsEnum::isOwnType($datas[0]),
        );
    }

    public function testGetDisplayAlgorithm(): void
    {
        $this->assertSame('sha-256', DigestAlgorithmsEnum::sha256->getDisplayAlgorithm());
        $this->assertSame('sha-512', DigestAlgorithmsEnum::sha512->getDisplayAlgorithm());
    }

    public function testGetPhpAlgorithm(): void
    {
        $this->assertSame('sha256', DigestAlgorithmsEnum::sha256->getPhpAlgorithm());
        $this->assertSame('sha512', DigestAlgorithmsEnum::sha512->getPhpAlgorithm());
    }
}
