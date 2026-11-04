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

use Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidHeaderKeyException::class)]
final class InvalidHeaderKeyExceptionTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     */
    public function testHeaderNotExists(): void
    {
        $this->expectExceptionObject(
            new InvalidHeaderKeyException(
                'The header "test-header-name" does not exist in the message. Note:headers can only be a part of the signing process if they are existing. $message->withHeader("test-header-name", \'your value\')',
            ),
        );
        throw InvalidHeaderKeyException::headerNotExists('test-header-name');
    }
}
