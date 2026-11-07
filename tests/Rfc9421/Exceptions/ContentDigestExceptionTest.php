<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Exceptions;

use Filefabrik\Rfc9421\Exceptions\ContentDigestException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContentDigestException::class)]
final class ContentDigestExceptionTest extends TestCase
{
    /**
     * @throws ContentDigestException
     */
    public function testBodyTempered(): void
    {
        $this->expectExceptionObject(new ContentDigestException('Body was tempered'));
        throw ContentDigestException::bodyTempered();
    }

    /**
     * @throws ContentDigestException
     */
    public function testWrongInputFormat(): void
    {
        $this->expectExceptionObject(new ContentDigestException('Wrong content digest format'));
        throw ContentDigestException::wrongInputFormat();
    }

    /**
     * @throws ContentDigestException
     */
    public function testHashAlgoNotSupported(): void
    {
        $this->expectExceptionObject(
            new ContentDigestException('Hash algorithm "something" for the content-digest not supported.'),
        );
        throw ContentDigestException::hashAlgoNotSupported('something');
    }


}
