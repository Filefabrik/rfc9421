<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Signer;

use Filefabrik\Rfc9421\Exceptions\ContentDigestException;
use Filefabrik\Rfc9421\Signer\ContentDigestHeader;
use Filefabrik\Rfc9421\SigningConfiguration;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContentDigestHeader::class)]
final class ContentDigestHeaderTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     */
    public function testHandle(): void
    {
        $messageInterface = new ContentDigestHeader(
            new Request('POST', 'test', [], 'text-body'),
            new SigningConfiguration()
                ->setContentDigestAlgo('sha256'),
        )->handle();

        self::assertSame(
            ['sha-256=:P1j8Gi6fRMQMICt/6yl36zNldruArKCCc8hd4kRADUA=:'],
            $messageInterface->getHeader('content-digest'),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     */
    public function testHandleThrowsNoAlgo(): void
    {
        $this->expectExceptionObject(ContentDigestException::canNotHashWithoutAlgorithm());
        new ContentDigestHeader(
            new Request('POST', 'test', [], 'text-body'),
            new SigningConfiguration(),
        )->handle();
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     */
    public function testContentDigestThrowAlreadySet(): void
    {
        $this->expectExceptionObject(ContentDigestException::headerContentDigestAlreadySet());
        new ContentDigestHeader(
            new Request('POST', 'test', ['content-digest' => 'bullshit and not validated at this point'], 'text-body'),
            new SigningConfiguration(),
        )->handle();
    }
}
