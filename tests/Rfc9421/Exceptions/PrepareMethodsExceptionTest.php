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

use Filefabrik\Rfc9421\Exceptions\PrepareMethodsException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

#[CoversClass(PrepareMethodsException::class)]
final class PrepareMethodsExceptionTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     */
    public function testMethodNotRegistered(): void
    {
        $this->expectExceptionObject(
            new PrepareMethodsException('Method "missing" not registered.'),
        );
        throw PrepareMethodsException::methodNotRegistered('missing');
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     */
    public function testErrorFromCalling(): void
    {
        $this->expectExceptionObject(
            new PrepareMethodsException('Error during calling the method with key: "buggy-key": is a bug inside'),
        );

        $throwable = new UnexpectedValueException('is a bug inside');
        throw PrepareMethodsException::errorFromCalling('buggy-key', $throwable);
    }
}
