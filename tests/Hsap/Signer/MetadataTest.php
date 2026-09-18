<?php

declare(strict_types=1);

namespace Filefabrik\HsapTest\Signer;

use BadMethodCallException;
use DateTime;
use Filefabrik\Hsap\Signer\Metadata;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Metadata::class)]
final class MetadataTest extends TestCase
{
    public static function dataProviderToArray(): array
    {
        return [
            'set all'           =>
                [
                    'created' => new DateTime()->getTimestamp(),
                    'expires' => new DateTime()->getTimestamp(),
                    'nonce'   => 'my-nonce',
                    'keyid'   => 'my-keyid',
                    'alg'     => 'my-alg',
                    'tag'     => 'my-tag',
                ],
            'set without dates' =>
                [

                    'nonce' => 'my-nonce',
                    'keyid' => 'my-keyid',
                    'alg'   => 'my-alg',
                    'tag'   => 'my-tag',
                ],
        ];
    }

    public function testSet(): void
    {
        $this->expectExceptionObject(
            new BadMethodCallException('Method not exists "setCrea($value)".'),
        );
        new Metadata()->set('crea', '');
    }

    public function testToString(): void
    {
        $now         = new DateTime()->getTimestamp();
        $stringified = Metadata::fromArray([
            'created' => $now,
            'expires' => $now,
            'nonce'   => 'my-nonce',
            'keyid'   => 'my-keyid',
            'alg'     => 'my-alg',
            'tag'     => 'my-tag',
        ])
                               ->toString()
        ;
        $this->assertEquals(
            'created='.$now.';'.'expires='.$now.';'.'nonce="my-nonce";keyid="my-keyid";alg="my-alg";tag="my-tag"',
            $stringified,
        );
    }

    /**
     * @covers ::toString
     * @return void
     */
    public function testToStringEmpty(): void
    {
        $stringified = Metadata::fromArray([])
                               ->toString()
        ;
        $this->assertEquals(
            '',
            $stringified,
        );
    }

    public function testSetTag(): void
    {
        $this->assertEquals('my-tag', new Metadata()->setTag('my-tag')->tag);
    }

    #[DataProvider('dataProviderToArray')]
    public function testToArray(...$datas): void
    {
        $this->assertSame(
            $datas,
            Metadata::fromArray($datas)
                    ->toArray(),
        );
    }

    public function testSetExpires(): void
    {
        $ts = new DateTime()->getTimestamp();

        $this->assertEquals($ts, new Metadata()->setExpires($ts)->expires);
    }

    public function testSetCreated(): void
    {
        $ts = new DateTime()->getTimestamp();

        $this->assertEquals($ts, new Metadata()->setCreated($ts)->created);
    }

    public function testSetAlg(): void
    {
        $this->assertEquals('my-algo', new Metadata()->setAlg('my-algo')->alg);
    }

    public function testSetNonce(): void
    {
        $this->assertEquals('my-nonce', new Metadata()->setNonce('my-nonce')->nonce);
    }

    public function testSetKeyid(): void
    {
        $this->assertEquals('my-keyid', new Metadata()->setKeyid('my-keyid')->keyid);
    }
}
