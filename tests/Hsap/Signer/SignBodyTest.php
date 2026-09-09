<?php

declare(strict_types=1);

namespace Filefabrik\HsapTest\Signer;

use Filefabrik\Hsap\Signer\SignBody;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SignBody::class)]
final class SignBodyTest extends TestCase
{
    public function testGetContentDigest(): void
    {
        $arr  = ['recipient' => 'Alex', 'amount' => 900000000];
        $body = json_encode($arr);
        $sig  = new SignBody($body, 'sha512')->getContentDigest();
        $this->assertSame(
            'sha512=:PlrIZYU3M76B30wGsL0h6O79BoxHTdAG+RnMPjOyECTSJCN/KnYdOrSCCWjxV3ckkyvdRmZ52//M3WbehCXcPw==:',
            $sig,
        );
    }

    public function testCalculateSignature(): void
    {
        $arr  = ['recipient' => 'Alex', 'amount' => 900000000];
        $body = json_encode($arr);
        $sig  = new SignBody($body, 'sha512')->calculateSignature();
        $this->assertSame(
            'PlrIZYU3M76B30wGsL0h6O79BoxHTdAG+RnMPjOyECTSJCN/KnYdOrSCCWjxV3ckkyvdRmZ52//M3WbehCXcPw==',
            $sig,
        );
    }

    public function testGetContentDigestHeaderArray(): void
    {
        $arr  = ['recipient' => 'Alex', 'amount' => 900000000];
        $body = json_encode($arr);
        $sig  = new SignBody($body, 'sha512')->getContentDigestHeaderArray();
        $this->assertSame(
            [
                'content-digest',
                'sha512=:PlrIZYU3M76B30wGsL0h6O79BoxHTdAG+RnMPjOyECTSJCN/KnYdOrSCCWjxV3ckkyvdRmZ52//M3WbehCXcPw==:',
            ],
            $sig,
        );
    }
}
