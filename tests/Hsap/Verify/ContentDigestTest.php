<?php

declare(strict_types=1);

namespace Filefabrik\HsapTest\Verify;

use Filefabrik\Hsap\Extractors\ContentDigest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Filefabrik\Hsap\Verify\ContentDigest::class)]
final class ContentDigestTest extends TestCase
{
    public function testFromContentDigestExtractor(): void
    {
        $body                     = '{"recipient":"Alex","amount":900000000}';
        $contentDigestHeaderValue =
            'sha512=:PlrIZYU3M76B30wGsL0h6O79BoxHTdAG+RnMPjOyECTSJCN/KnYdOrSCCWjxV3ckkyvdRmZ52//M3WbehCXcPw==:';
        $contentDigest            = new ContentDigest($contentDigestHeaderValue);

        $res = \Filefabrik\Hsap\Verify\ContentDigest::fromContentDigestExtractor($body, $contentDigest)
                                                    ->verify()
        ;
        $this->assertTrue($res);
    }

}
