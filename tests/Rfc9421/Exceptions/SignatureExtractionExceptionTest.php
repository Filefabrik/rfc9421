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

use Filefabrik\Rfc9421\Exceptions\SignatureExtractionException;
use Filefabrik\Rfc9421\Verify\HeaderSignatureInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SignatureExtractionException::class)]
final class SignatureExtractionExceptionTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testNotProceeded(): void
    {
        $this->expectExceptionObject(
            new SignatureExtractionException(
                'Signature is not proceeded. Call before '.HeaderSignatureInput::class.'::extract',
            ),
        );
        throw SignatureExtractionException::notProceeded();
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testInvalidLine(): void
    {
        $this->expectExceptionObject(
            new SignatureExtractionException(
                'Invalid signature line:"@params-dings":',
            ),
        );
        throw SignatureExtractionException::invalidLine('"@params-dings":');
    }
}
