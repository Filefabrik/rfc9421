<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Signature;

use Filefabrik\Rfc9421\Signature\DerivedComponentEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DerivedComponentEnum::class)]
final class DerivedComponentEnumTest extends TestCase
{
    public function testIsOwnValueOrNull(): void
    {
        self::assertNull(DerivedComponentEnum::isOwnValueOrNull('nullvalue'));
        self::assertSame(DerivedComponentEnum::status, DerivedComponentEnum::isOwnValueOrNull('@status'));
        self::assertSame(
            DerivedComponentEnum::authority,
            DerivedComponentEnum::isOwnValueOrNull(DerivedComponentEnum::authority),
        );
    }

    public function testValues(): void
    {
        $expected = [];
        foreach (DerivedComponentEnum::cases() as $item) {
            $expected[] = $item->value;
        }

        self::assertEquals(
            [
                '@signature-params',
                '@method',
                '@authority',
                '@scheme',
                '@target-uri',
                '@request-target',
                '@path',
                '@query',
                '@query-param',
                '@status',
            ],
            $expected,
        );
    }
}
