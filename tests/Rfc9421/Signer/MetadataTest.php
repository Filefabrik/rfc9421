<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Signer;

use BadMethodCallException;
use DateTime;
use Filefabrik\Rfc9421\Cryptography\SigningEnum;
use Filefabrik\Rfc9421\Signer\Metadata;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Metadata::class)]
#[\PHPUnit\Framework\Attributes\CoversMethod(Metadata::class,'toString')]
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
                    'alg'     => 'hmac-sha512',
                    'tag'     => 'my-tag',
                ],
            'set without dates' =>
                [

                    'nonce' => 'my-nonce',
                    'keyid' => 'my-keyid',
                    'alg'   => 'hmac-sha512',
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
            'alg'     => 'hmac_sha_512',
            'tag'     => 'my-tag',
        ])
                               ->toString()
        ;
        $this->assertEquals(
            'created='.$now.';'.'expires='.$now.';'.'nonce="my-nonce";keyid="my-keyid";alg="hmac-sha512";tag="my-tag"',
            $stringified,
        );
    }

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
        $this->assertEquals(SigningEnum::ecdsa_p_384_sha_384, new Metadata()->setAlg('ecdsa_p_384_sha_384')->alg);
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
