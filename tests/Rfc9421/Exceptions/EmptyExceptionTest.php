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

use Filefabrik\Rfc9421\Exceptions\EmptyException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EmptyException::class)]
final class EmptyExceptionTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EmptyException
     */
    public function testPublicPemIsEmpty(): void
    {
        $this->expectExceptionObject(new EmptyException('Public pem is empty.'));
        throw EmptyException::publicPemIsEmpty();
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EmptyException
     */
    public function testPrivatePemIsEmpty(): void
    {
        $this->expectExceptionObject(new EmptyException('Private pem is empty.'));
        throw EmptyException::privatePemIsEmpty();
    }
}
