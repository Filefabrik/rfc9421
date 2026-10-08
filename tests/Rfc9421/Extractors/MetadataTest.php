<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Extractors;

use DateTime;
use Filefabrik\Rfc9421\Extractors\Metadata;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Metadata::class)]
final class MetadataTest extends TestCase
{
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

        $this->assertSame($res->toArray(), $expectation);
    }

    public function testExtractThrowParts(): void
    {
        $this->expectExceptionObject(
            new \InvalidArgumentException('Invalid segment: nonce=="my-nonce". Expects "key=value"'),
        );
        $now    = new DateTime()->getTimestamp();
        $string =
            'created='.$now.';'.'expires='.$now.';'.'nonce=="my-nonce";keyid="my-keyid";algorithm="my-algorithm";tag="my-tag"';

        Metadata::stringToMetadata($string);
    }

    public function testExtractThrowDuplicate(): void
    {
        $this->expectExceptionObject(new \InvalidArgumentException('Key was already set before segment: nonce.'));
        $now    = new DateTime()->getTimestamp();
        $string =
            'created='.$now.';'.'expires='.$now.';'.'nonce="my-nonce";keyid="my-keyid";algorithm="my-algorithm";tag="my-tag";nonce="my-nonce"';

        Metadata::stringToMetadata($string);
    }

    public function testExtractEmptyCreated(): void
    {
        $now     = new DateTime()->getTimestamp();
        $string  = 'created=;'.'expires='.$now.';'.'nonce="my-nonce";keyid="my-keyid";tag="my-tag"';
        $created = Metadata::stringToMetadata($string)->created;
        $this->assertEquals(0, $created);
    }

    public function testExtractZeroCreated(): void
    {
        $now     = new DateTime()->getTimestamp();
        $string  = 'created=0;'.'expires='.$now.';'.'nonce="my-nonce";keyid="my-keyid";tag="my-tag"';
        $created = Metadata::stringToMetadata($string)->created;
        $this->assertEquals(0, $created);
    }

}
