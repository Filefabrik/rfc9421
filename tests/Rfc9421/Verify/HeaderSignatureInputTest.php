<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Verify;

use Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException;
use Filefabrik\Rfc9421\Verify\HeaderSignatureInput;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(HeaderSignatureInput::class)]
final class HeaderSignatureInputTest extends TestCase
{
    public function testExtract(): void
    {
        $expectedComponents   = ['@method', 'content-digest'];
        $expectedMetadata     = [
            'created' => 1789749530,
            'expires' => 1789756730,
            'nonce'   => 'nonce-now',
            'tag'     => 'mastodon',
        ];
        $sig                  =
            'someSig=("@method" "content-digest");created=1789749530;expires=1789756730;nonce="nonce-now";tag="mastodon"';
        $headerSignatureInput = new HeaderSignatureInput($sig, 'someSig=');


        $headerSignatureInput->extract();

        self::assertSame($expectedComponents, $headerSignatureInput->extractedComponents);
        self::assertSame($expectedMetadata, $headerSignatureInput->extractedMetadata->toArray());
    }

    public function testExtractWithoutKey(): void
    {
        $expectedComponents   = ['@method', 'content-digest'];
        $expectedMetadata     = [
            'created' => 1789749530,
            'expires' => 1789756730,
            'nonce'   => 'nonce-now',
            'tag'     => 'mastodon',
        ];
        $sig                  =
            '("@method" "content-digest");created=1789749530;expires=1789756730;nonce="nonce-now";tag="mastodon"';
        $headerSignatureInput = new HeaderSignatureInput($sig, '');


        $headerSignatureInput->extract();

        self::assertSame($expectedComponents, $headerSignatureInput->extractedComponents);
        self::assertSame($expectedMetadata, $headerSignatureInput->extractedMetadata->toArray());
    }

    public function testExtractWithSigKeyThrowsException(): void
    {
        $this->expectExceptionObject(
            new VerifyHeaderSignatureException(
                'Error during extracting the signatureKeyName from signature-input. Expects the keyname: "someSig=" in header: "error=("@method" "content-digest");created=1789749530;expires=1789756730;nonce="nonce-now";tag="mastodon"',
            ),
        );
        $sig                  =
            'error=("@method" "content-digest");created=1789749530;expires=1789756730;nonce="nonce-now";tag="mastodon"';
        $headerSignatureInput = new HeaderSignatureInput($sig, 'someSig=');
        $headerSignatureInput->extract();
    }

    public function testExtractMissingSignatureComponents(): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('missing components'));

        $sig                  =
            'someSig=();created=1789749530;expires=1789756730;nonce="nonce-now";tag="mastodon"';
        $headerSignatureInput = new HeaderSignatureInput($sig, 'someSig=');


        $headerSignatureInput->extract();
    }

    public function testExtractInvalidSignature(): void
    {
        $this->expectExceptionObject(
            new VerifyHeaderSignatureException(
                'Invalid signature line ");created=1789749530;expires=1789756730;nonce="nonce-now";tag="mastodon""',
            ),
        );

        $sig                  =
            'someSig=);created=1789749530;expires=1789756730;nonce="nonce-now";tag="mastodon"';
        $headerSignatureInput = new HeaderSignatureInput($sig, 'someSig=');


        $headerSignatureInput->extract();
    }
}
