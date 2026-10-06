<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Verify;

use Filefabrik\Rfc9421\Extractors\ContentDigest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Filefabrik\Rfc9421\Verify\ContentDigest::class)]
final class ContentDigestTest extends TestCase
{
    public function testFromContentDigestExtractor(): void
    {
        $body                     = '{"recipient":"Alex","amount":900000000}';
        $contentDigestHeaderValue =
            'sha512=:PlrIZYU3M76B30wGsL0h6O79BoxHTdAG+RnMPjOyECTSJCN/KnYdOrSCCWjxV3ckkyvdRmZ52//M3WbehCXcPw==:';
        $contentDigest            = new ContentDigest($contentDigestHeaderValue);

        $res = \Filefabrik\Rfc9421\Verify\ContentDigest::fromContentDigestExtractor(
            body                  : $body,
            contentDigestExtractor: $contentDigest,
        )
                                                       ->verify()
        ;
        $this->assertTrue($res);
    }

}
