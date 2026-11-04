<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * todo insert correct copyright
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Enums;

use Filefabrik\Rfc9421\Enums\DigestAlgorithmsEnum;
use Filefabrik\Rfc9421\Exceptions\ContentDigestException;
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
            ['sha-512', DigestAlgorithmsEnum::sha512],
            [DigestAlgorithmsEnum::sha512, DigestAlgorithmsEnum::sha512],
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
        self::assertSame(
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

    #[DataProvider('validDigestAlgorithms')]
    public function testIsOwnType(...$datas): void
    {
        self::assertSame(
            $datas[1],
            DigestAlgorithmsEnum::isOwnType($datas[0]),
        );
    }

    #[DataProvider('invalidDigestAlgorithms')]
    public function testIsOwnTypeNull(...$datas): void
    {
        self::assertNull(
            DigestAlgorithmsEnum::isOwnType($datas[0])
        );
    }

    public function testGetDisplayAlgorithm(): void
    {
        self::assertSame('sha-256', DigestAlgorithmsEnum::sha256->getDisplayAlgorithm());
        self::assertSame('sha-512', DigestAlgorithmsEnum::sha512->getDisplayAlgorithm());
    }

    public function testGetPhpAlgorithm(): void
    {
        self::assertSame('sha256', DigestAlgorithmsEnum::sha256->getPhpAlgorithm());
        self::assertSame('sha512', DigestAlgorithmsEnum::sha512->getPhpAlgorithm());
    }
}
