<?php
/*
 * Copyright (c) 2026.  rt@filefabrik.com
 * proprietary
 * todo insert correct copyright
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Exceptions;

use Filefabrik\Rfc9421\Exceptions\SignatureExtractionException;
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
                'Signature is not proceeded. Call before Filefabrik\Rfc9421\Verify\HeaderSignatureInput::extract',
            ),
        );
        throw SignatureExtractionException::notProceeded();
    }
}
