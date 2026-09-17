<?php

declare(strict_types=1);

namespace Filefabrik\HsapTest\Verify;

use Filefabrik\Hsap\Exceptions\VerifyHeaderSignatureException;
use Filefabrik\Hsap\Verify\HeaderSignatureInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

#[CoversClass(HeaderSignatureInput::class)]
final class HeaderSignatureInputTest extends TestCase
{
    public function testExtract(): void
    {
        $sig                  = 'someSig=("test" "@test" "try-out" "some-header")';
        $headerSignatureInput = new HeaderSignatureInput($sig, 'someSig=');
        $this->assertSame(['test', '@test', 'try-out', 'some-header'], $headerSignatureInput->extract());
    }

    public function testExtractSignatureLies(): void
    {
        $this->expectExceptionObject(
            new VerifyHeaderSignatureException(
                'Error during extracting the signatureKeyName from signature-input. Expects the keyname: "iamlieing=" in header: "someSig=("test" "@test" "try-out" "some-header")"',
            ),
        );
        $sig = 'someSig=("test" "@test" "try-out" "some-header")';
        new HeaderSignatureInput($sig, 'iamlieing=')->extract();
    }

    public function testExtractSignatureEmptyKeyButNeeds(): void
    {
        $this->expectExceptionObject(
            new UnexpectedValueException(
                'Unexpected start end of field.',
            ),
        );
        $sig = 'someSig=("test" "@test" "try-out" "some-header")';
        new HeaderSignatureInput($sig, '')->extract();
    }
}
