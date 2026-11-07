<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Verify;

use Filefabrik\Rfc9421\Exceptions\ContentDigestException;
use Filefabrik\Rfc9421\Verify\ContentDigest;
use Filefabrik\Rfc9421\Verify\VerifyContentDigest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(VerifyContentDigest::class)]
final class VerifyContentDigestTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     */
    public function testVerify(): void
    {
        $CD   = 'sha-512=:ky7+tHa2jKBwuLnRdgxC418SwYUSuSk4NXiGsAYA6aKby9eVl2PTRsVjwEn1EjPQ93BxKbqzkCh58NqE1nL67A==:';
        $body = '{"recipient":"Alex","amount":900000000,"text":"fdawsfs"}';

        $verify = new VerifyContentDigest($CD, $body);
        self::assertTrue($verify->verify());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     */
    public function testVerifyFalse(): void
    {
        $CD   = 'sha-512=:ky7+tHa2jKBwuLnRdgxC418SwYUSuSk4NXiGsAYA6aKby9eVl2PTRsVjwEn1EjPQ93BxKbqzkCh58NqE1nL67A==:';
        $body = '{"recipient":"Alex","amount":900000000,"text":"fdawsfsWRONG"}';

        $verify = new VerifyContentDigest($CD, $body);
        self::assertFalse($verify->verify());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     */
    public function testVerifyThrowsFormatIssue(): void
    {
        $this->expectExceptionObject(new ContentDigestException('Wrong content digest format'));
        $CD   = '';
        $body = '{"recipient":"Alex","amount":900000000,"text":"fdawsfs"}';

        $verify = new VerifyContentDigest($CD, $body);
        $verify->prepare();
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     */
    public function testPrepare(): void
    {
        $CD   = 'sha-512=:ky7+tHa2jKBwuLnRdgxC418SwYUSuSk4NXiGsAYA6aKby9eVl2PTRsVjwEn1EjPQ93BxKbqzkCh58NqE1nL67A==:';
        $body = '{"recipient":"Alex","amount":900000000,"text":"fdawsfs"}';

        $verify   = new VerifyContentDigest($CD, $body);
        $prepared = $verify->prepare();

        self::assertEquals(
            new ContentDigest(
                $body,
                'sha512',
                'ky7+tHa2jKBwuLnRdgxC418SwYUSuSk4NXiGsAYA6aKby9eVl2PTRsVjwEn1EjPQ93BxKbqzkCh58NqE1nL67A==',
            ),
            $prepared,
        );
    }

    /*
     * todo testing validatedSignatureParts error

        public function testPrepareWrongFormat(): void
        {
            $this->expectExceptionObject(new ContentDigestException('Wrong content digest format'));
            $CD   = ':ky7+tHa2jKBwuLnRdgxC418SwYUSuSk4NXiGsAYA6aKby9eVl2PTRsVjwEn1EjPQ93BxKbqzkCh58NqE1nL67A==:';
            $body = '{"recipient":"Alex","amount":900000000,"text":"fdawsfs"}';

            $verify = new VerifyContentDigest($CD, $body);
            $verify->prepare();
        }
    */
}
