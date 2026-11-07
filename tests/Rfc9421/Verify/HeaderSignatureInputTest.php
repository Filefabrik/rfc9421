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

use Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException;
use Filefabrik\Rfc9421\Signature\SigningComponents;
use Filefabrik\Rfc9421\Verify\HeaderSignatureInput;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(HeaderSignatureInput::class)]
final class HeaderSignatureInputTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public function testExtract(): void
    {
        $expectedComponents   = new SigningComponents()->addName('@method')
                                                       ->addName('content-digest')
        ;
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

        self::assertEquals($expectedComponents, $headerSignatureInput->getExtractedComponents());
        self::assertSame($expectedMetadata, $headerSignatureInput->extractedMetadata->toArray());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public function testExtractWithoutKey(): void
    {
        $expectedComponents   = new SigningComponents()->addName('@method')
                                                       ->addName('content-digest')
        ;
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

        self::assertEquals($expectedComponents, $headerSignatureInput->getExtractedComponents());
        self::assertSame($expectedMetadata, $headerSignatureInput->extractedMetadata->toArray());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
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

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public function testExtractMissingSignatureComponents(): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('missing components'));

        $sig                  =
            'someSig=();created=1789749530;expires=1789756730;nonce="nonce-now";tag="mastodon"';
        $headerSignatureInput = new HeaderSignatureInput($sig, 'someSig=');

        $headerSignatureInput->extract();
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
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
