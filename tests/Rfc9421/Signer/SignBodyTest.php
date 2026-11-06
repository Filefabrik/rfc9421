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

use Filefabrik\Rfc9421\Signer\SignBody;
use JsonException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SignBody::class)]
final class SignBodyTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws JsonException
     */
    public function testGetContentDigest(): void
    {
        $arr  = ['recipient' => 'Alex', 'amount' => 900000000];
        $body = json_encode($arr, JSON_THROW_ON_ERROR);
        $sig  = new SignBody($body, 'sha512')->getContentDigest();
        self::assertSame(
            'sha-512=:PlrIZYU3M76B30wGsL0h6O79BoxHTdAG+RnMPjOyECTSJCN/KnYdOrSCCWjxV3ckkyvdRmZ52//M3WbehCXcPw==:',
            $sig,
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws JsonException
     */
    public function testCalculateSignature(): void
    {
        $arr  = ['recipient' => 'Alex', 'amount' => 900000000];
        $body = json_encode($arr, JSON_THROW_ON_ERROR);
        $sig  = new SignBody($body, 'sha512')->calculateSignature();
        self::assertSame(
            'PlrIZYU3M76B30wGsL0h6O79BoxHTdAG+RnMPjOyECTSJCN/KnYdOrSCCWjxV3ckkyvdRmZ52//M3WbehCXcPw==',
            $sig,
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws JsonException
     */
    public function testGetContentDigestHeaderArray(): void
    {
        $arr  = ['recipient' => 'Alex', 'amount' => 900000000];
        $body = json_encode($arr, JSON_THROW_ON_ERROR);
        $sig  = new SignBody($body, 'sha512')->getContentDigestHeaderArray();
        self::assertSame(
            [
                'content-digest',
                'sha-512=:PlrIZYU3M76B30wGsL0h6O79BoxHTdAG+RnMPjOyECTSJCN/KnYdOrSCCWjxV3ckkyvdRmZ52//M3WbehCXcPw==:',
            ],
            $sig,
        );
    }
}
