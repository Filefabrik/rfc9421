<?php

declare(strict_types=1);

namespace Filefabrik\HsapTest\Verify;

use Filefabrik\Hsap\Exceptions\VerifyHeaderSignatureException;
use Filefabrik\Hsap\Verify\HeaderSignature;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(HeaderSignature::class)]
final class HeaderSignatureTest extends TestCase
{
    public function testValidateT(): void
    {
        $this->expectNotToPerformAssertions();
        $r = new Request(
            method : 'POST',
            uri    : 'https://test',
            headers: [
                'x-Heading-Test'  => 'test-value',
                'signature'       => 'siggi=:crypt:',
                'signature-input' => 'siggi=("x-heading-test" "content-digest");alg="sha512"',
                'content-digest'  => 'sha512=:PlrIZYU3M76B30wGsL0h6O79BoxHTdAG+RnMPjOyECTSJCN/KnYdOrSCCWjxV3ckkyvdRmZ52//M3WbehCXcPw==:',
            ],
            body   : '{"recipient":"Alex","amount":900000000}',
        );
        // sha512=:PlrIZYU3M76B30wGsL0h6O79BoxHTdAG+RnMPjOyECTSJCN/KnYdOrSCCWjxV3ckkyvdRmZ52//M3WbehCXcPw==:
        $instance            = new HeaderSignature($r);
        $instance->publicPem = <<<text
-----BEGIN PUBLIC KEY-----
MIGbMBQGByqGSM49AgEGCSskAwMCCAEBDgOBggAEmn3TVHPwtiGXHm8ss3FXA9J4
Bp+emMUaLO3Fn9RZ4eYk/ZLPoFlanf9go/1nrIfWFYLHmIQJGkojkdcxhKQ3oReS
W+4oYUgBTZe9QXJmntO0qmtY4EdmScMucHmOtfo4SovgL47u0jReWH/X8DVYCQW+
BtU/LEXIVIoMBeXkxac=
-----END PUBLIC KEY-----

text;
        //$instance->validate();
    }

    public function testValidateThrowMissingSignature(): void
    {
        $this->expectExceptionObject(new VerifyHeaderSignatureException('Missing signature'));
        $r = new Request(
            method : 'POST',
            uri    : 'https://test',
            headers: [
            ],
        );
        new HeaderSignature($r)->validate();
    }

    public function testValidateThrowMissingSignatureInput(): void
    {
        $this->expectExceptionObject(new VerifyHeaderSignatureException('Missing signature-input'));
        $r = new Request(
            method : 'POST',
            uri    : 'https://test',
            headers: [
                'signature' => 'test=:crypt:',
            ],
        );
        new HeaderSignature($r)->validate();
    }

    public function testValidateThrowHeaderKeyName(): void
    {
        $this->expectExceptionObject(
            new VerifyHeaderSignatureException('Error during extracting the signatureKeyName'),
        );
        $r        = new Request(
            method : 'POST',
            uri    : 'https://test',
            headers: [
                'signature'       => 'crypt:',
                'signature-input' => 'atmNotRelevant',
            ],
        );
        $instance = new HeaderSignature($r);
        $instance->validate();
    }

}
