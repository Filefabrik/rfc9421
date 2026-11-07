<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Signer;

use BadMethodCallException;
use DateTime;
use Filefabrik\Rfc9421\Enums\SigningEnum;
use Filefabrik\Rfc9421\Signer\Metadata;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Metadata::class)]
final class MetadataTest extends TestCase
{
    /**
     * @return array{"set all": array{created: int, expires: int, nonce: string, keyid: string, alg: string, tag: string}, "set without dates": array{nonce: string, keyid: string, alg: string, tag: string}}
     */
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

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public function testSet(): void
    {
        $this->expectExceptionObject(
            new BadMethodCallException('Method not exists "setCrea($value)".'),
        );
        new Metadata()->set('crea', '');
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
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
        self::assertEquals(
            'created='.$now.';'.'expires='.$now.';'.'nonce="my-nonce";keyid="my-keyid";alg="hmac-sha512";tag="my-tag"',
            $stringified,
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public function testToStringEmpty(): void
    {
        $stringified = Metadata::fromArray([])
                               ->toString()
        ;
        self::assertEquals(
            '',
            $stringified,
        );
    }

    public function testSetTag(): void
    {
        self::assertEquals('my-tag', new Metadata()->setTag('my-tag')->tag);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    #[DataProvider('dataProviderToArray')]
    public function testToArray(...$datas): void
    {
        self::assertSame(
            $datas,
            Metadata::fromArray($datas)
                    ->toArray(),
        );
    }

    public function testSetExpires(): void
    {
        $ts = new DateTime()->getTimestamp();

        self::assertEquals($ts, new Metadata()->setExpires($ts)->expires);
    }

    public function testSetCreated(): void
    {
        $ts = new DateTime()->getTimestamp();

        self::assertEquals($ts, new Metadata()->setCreated($ts)->created);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public function testSetAlg(): void
    {
        self::assertEquals(SigningEnum::ecdsa_p_384_sha_384, new Metadata()->setAlg('ecdsa_p_384_sha_384')->alg);
    }

    public function testSetNonce(): void
    {
        self::assertEquals('my-nonce', new Metadata()->setNonce('my-nonce')->nonce);
    }

    public function testSetKeyid(): void
    {
        self::assertEquals('my-keyid', new Metadata()->setKeyid('my-keyid')->keyid);
    }

    public function testHasTag(): void
    {
        self::assertTrue(
            new Metadata()->setTag('my-tag')
                          ->hasTag(),
        );
        self::assertFalse(new Metadata()->hasTag());
    }
}
