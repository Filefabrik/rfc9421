<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Support;

use Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException;
use Filefabrik\Rfc9421\Support\SignaturInput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SignaturInput::class)]
final class SignaturInputTest extends TestCase
{
    /**
     * @throws VerifyHeaderSignatureException
     */
    public function testEmpty(): void
    {
        $s = '';
        $this->expectExceptionObject(VerifyHeaderSignatureException::invalidSignatureLine($s));

        SignaturInput::extract($s);
    }

    public function testComponentsOnly(): void
    {
        $s        = '("@method" "content-digest")';
        $instance = SignaturInput::extract($s);

        $this->assertSame('', $instance->signatureKeyName);
        $this->assertSame('"@method" "content-digest"', $instance->components);
        $this->assertSame('', $instance->metadata);
    }

    public function testExtractingWithoutMetadataNaked(): void
    {
        $s        =
            'testValue=("@method" "content-digest");created=1789634264;expires=1789641464;nonce="nonce-now";tag="mastodon"';
        $instance = SignaturInput::extract($s);

        $this->assertSame('testValue=', $instance->signatureKeyName);
        $this->assertSame('"@method" "content-digest"', $instance->components);
        $this->assertSame(
            'created=1789634264;expires=1789641464;nonce="nonce-now";tag="mastodon"',
            $instance->metadata,
        );
    }

    public function testExtractingWithoutMetadata(): void
    {
        $s        = 'testValue=("@method" "content-digest");';
        $instance = SignaturInput::extract($s);
        $this->assertSame('testValue=', $instance->signatureKeyName);
        $this->assertSame('"@method" "content-digest"', $instance->components);
        $this->assertSame('', $instance->metadata);
    }
}
