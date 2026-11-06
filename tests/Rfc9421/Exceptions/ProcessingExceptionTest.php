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

use Filefabrik\Rfc9421\Exceptions\ProcessingException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProcessingException::class)]
final class ProcessingExceptionTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ProcessingException
     */
    public function testSignedHeaderLines(): void
    {
        $this->expectExceptionObject(
            new ProcessingException(
                'VerifyRequest->getSignedHeaderLines() can be called only after VerifyRequest->verifying()',
            ),
        );
        throw ProcessingException::SignedHeaderLines();
    }
}
