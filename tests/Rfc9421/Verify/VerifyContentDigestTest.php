<?php

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
    public function testVerify(): void
    {
        $CD   = 'sha-512=:ky7+tHa2jKBwuLnRdgxC418SwYUSuSk4NXiGsAYA6aKby9eVl2PTRsVjwEn1EjPQ93BxKbqzkCh58NqE1nL67A==:';
        $body = '{"recipient":"Alex","amount":900000000,"text":"fdawsfs"}';

        $verify = new VerifyContentDigest($CD, $body);
        $this->assertTrue($verify->verify());
    }

    public function testVerifyFalse(): void
    {
        $CD   = 'sha-512=:ky7+tHa2jKBwuLnRdgxC418SwYUSuSk4NXiGsAYA6aKby9eVl2PTRsVjwEn1EjPQ93BxKbqzkCh58NqE1nL67A==:';
        $body = '{"recipient":"Alex","amount":900000000,"text":"fdawsfsWRONG"}';

        $verify = new VerifyContentDigest($CD, $body);
        $this->assertFalse($verify->verify());
    }

    public function testVerifyThrowsFormatIssue(): void
    {
        $this->expectExceptionObject(new ContentDigestException('Wrong content digest format'));
        $CD   = '';
        $body = '{"recipient":"Alex","amount":900000000,"text":"fdawsfs"}';

        $verify = new VerifyContentDigest($CD, $body);
        $verify->prepare();
    }

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
     * todo testing extraction error

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
