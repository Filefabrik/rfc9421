<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * todo insert correct copyright
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Exceptions;

use Filefabrik\Rfc9421\Exceptions\SignatureException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SignatureException::class)]
final class SignatureExceptionTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public function testSignatureEnumNotFound(): void
    {
        $this->expectExceptionObject(
            new SignatureException('The signature enum "-bullshit-algo-" for signing is not supported.'),
        );
        throw SignatureException::signatureEnumNotFound('-bullshit-algo-');
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public function testWrongFormat(): void
    {
        $this->expectExceptionObject(
            new SignatureException('The signature algorithm must be a string|SigningEnumInterface.'),
        );
        throw SignatureException::wrongFormat();
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public function testNotSet(): void
    {
        $this->expectExceptionObject(new SignatureException('Signature algorithm must be set.'));
        throw SignatureException::notSet();
    }
}
