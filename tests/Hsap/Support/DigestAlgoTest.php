<?php

declare(strict_types=1);

namespace Filefabrik\HsapTest\Support;

use Filefabrik\Hsap\Exceptions\ContentDigestException;
use Filefabrik\Hsap\Support\DigestAlgo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(DigestAlgo::class)]
final class DigestAlgoTest extends TestCase
{
    public static function validDigestAlgorithms(): array
    {
        return [
            ['sha256' => 'sha256'],
            ['sha-256' => 'sha256'],
            ['sha512' => 'sha512'],
            ['sha-512' => 'sha512',],
            [null => null],
        ];
    }

    /**
     * @covers \Filefabrik\Hsap\Support\DigestAlgo::validateContentDigestHashAlgo
     * @return void
     * @throws ContentDigestException
     */
    public function testValidateContentDigestHashAlgoThrows(): void
    {
        $this->expectExceptionObject(
            new ContentDigestException('Hash algorithm "-bullshit-" for the content-digest not supported.'),
        );
        DigestAlgo::validateContentDigestHashAlgo('-bullshit-');
    }

    #[DataProvider('validDigestAlgorithms')]
    public function testValidateContentDigestHashAlgo(...$datas): void
    {
        $this->assertSame(
            $datas[1],
            DigestAlgo::validateContentDigestHashAlgo($datas[0]),
        );
    }

}
