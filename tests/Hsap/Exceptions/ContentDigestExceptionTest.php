<?php

declare(strict_types=1);

namespace Filefabrik\HsapTest\Exceptions;

use Filefabrik\Hsap\Exceptions\ContentDigestException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContentDigestException::class)]
final class ContentDigestExceptionTest extends TestCase
{
    public function testBodyTempered(): void
    {
        $this->expectExceptionObject(new ContentDigestException('Body was tempered'));
        throw ContentDigestException::bodyTempered();
    }

    public function testWrongInputFormat(): void
    {
        $this->expectExceptionObject(new ContentDigestException('Wrong content digest format'));
        throw ContentDigestException::wrongInputFormat();
    }
}
