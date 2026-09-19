<?php

declare(strict_types=1);

namespace Filefabrik\HsapTest\Extractors;

use DateTime;
use Filefabrik\Hsap\Extractors\Metadata;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Metadata::class)]
final class MetadataTest extends TestCase
{
    public function testExtract(): void
    {
        $now    = new DateTime()->getTimestamp();
        $string =
            'created='.$now.';'.'expires='.$now.';'.'nonce="my-nonce";keyid="my-keyid";alg="my-alg";tag="my-tag"';

        $expectation = [
            'created' => $now,
            'expires' => $now,
            'nonce'   => 'my-nonce',
            'keyid'   => 'my-keyid',
            'alg'     => 'my-alg',
            'tag'     => 'my-tag',
        ];

        $res = Metadata::stringToMetadata($string);

        $this->assertSame($res->toArray(), $expectation);
    }

    public function testExtractThrowParts(): void
    {
        $this->expectExceptionObject(new \InvalidArgumentException('Invalid segment: nonce=="my-nonce". Expects "key=value"'));
        $now    = new DateTime()->getTimestamp();
        $string =
            'created='.$now.';'.'expires='.$now.';'.'nonce=="my-nonce";keyid="my-keyid";alg="my-alg";tag="my-tag"';

        Metadata::stringToMetadata($string);
    }

    public function testExtractThrowDuplicate(): void
    {
        $this->expectExceptionObject(new \InvalidArgumentException('Key was already set before segment: nonce.'));
        $now    = new DateTime()->getTimestamp();
        $string =
            'created='.$now.';'.'expires='.$now.';'.'nonce="my-nonce";keyid="my-keyid";alg="my-alg";tag="my-tag";nonce="my-nonce"';

        Metadata::stringToMetadata($string);
    }
}
