<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Extractors;

use DateTime;
use Filefabrik\Rfc9421\Enums\SigningEnum;
use Filefabrik\Rfc9421\Exceptions\SignatureException;
use Filefabrik\Rfc9421\Extractors\Metadata;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Metadata::class)]
final class MetadataTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public function testExtract(): void
    {
        $now    = new DateTime()->getTimestamp();
        $string =
            'created='.$now.';'.'expires='.$now.';'.'nonce="my-nonce";keyid="my-keyid";alg="hmac_sha_512";tag="my-tag"';

        $expectation = [
            'created' => $now,
            'expires' => $now,
            'nonce'   => 'my-nonce',
            'keyid'   => 'my-keyid',
            'alg'     => 'hmac-sha512',
            'tag'     => 'my-tag',
        ];

        $res = Metadata::stringToMetadata($string);

        self::assertSame($res->toArray(), $expectation);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public function testExtractThrowParts(): void
    {
        $this->expectExceptionObject(
            new InvalidArgumentException('Invalid segment: nonce=="my-nonce". Expects "key=value"'),
        );
        $now    = new DateTime()->getTimestamp();
        $string =
            'created='.$now.';'.'expires='.$now.';'.'nonce=="my-nonce";keyid="my-keyid";algorithm="my-algorithm";tag="my-tag"';

        Metadata::stringToMetadata($string);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public function testExtractThrowDuplicate(): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('Key was already set before segment: nonce.'));
        $now    = new DateTime()->getTimestamp();
        $string =
            'created='.$now.';'.'expires='.$now.';'.'nonce="my-nonce";keyid="my-keyid";algorithm="my-algorithm";tag="my-tag";nonce="my-nonce"';

        Metadata::stringToMetadata($string);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public function testExtractEmptyCreated(): void
    {
        $now     = new DateTime()->getTimestamp();
        $string  = 'created=;'.'expires='.$now.';'.'nonce="my-nonce";keyid="my-keyid";tag="my-tag"';
        $created = Metadata::stringToMetadata($string)->created;
        self::assertEquals(0, $created);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public function testExtractZeroCreated(): void
    {
        $now     = new DateTime()->getTimestamp();
        $string  = 'created=0;'.'expires='.$now.';'.'nonce="my-nonce";keyid="my-keyid";tag="my-tag"';
        $created = Metadata::stringToMetadata($string)->created;
        self::assertEquals(0, $created);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public function testGetAlgOrThrow(): void
    {
        $i = Metadata::stringToMetadata('alg="'.SigningEnum::rsa_v_1_5_sha_256->getName().'"');
        self::assertSame(SigningEnum::rsa_v_1_5_sha_256, $i->getAlgOrThrow());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public function testGetAlgOrThrowThrows(): void
    {
        $this->expectExceptionObject(new SignatureException('Signature algorithm must be set.'));
        $i = new Metadata('');
        $i->getAlgOrThrow();
    }
}
