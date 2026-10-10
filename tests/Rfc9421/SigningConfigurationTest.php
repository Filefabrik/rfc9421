<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test;

use Filefabrik\Rfc9421\Cryptography\SigningEnum;
use Filefabrik\Rfc9421\Exceptions\ContentDigestException;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421\Support\DigestAlgorithmsEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SigningConfiguration::class)]
final class SigningConfigurationTest extends TestCase
{
    public function testWithTag(): void
    {
        $i = new SigningConfiguration();
        self::assertSame('tagname', $i->setTag('tagname')->metadata->tag);
        $i->setTag(null);
        self::assertNull($i->metadata->tag);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     */
    public function testGetContentDigestHashAlgo(): void
    {
        $i = new SigningConfiguration();
        self::assertNull($i->contentDigestAlgo);
        self::assertNull($i->getContentDigestAlgo());
        $i->setContentDigestAlgo('sha-512');
        self::assertSame(DigestAlgorithmsEnum::sha512, $i->contentDigestAlgo);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     */
    public function testHasContentDigestHashAlgo(): void
    {
        $i = new SigningConfiguration();

        self::assertFalse($i->hasContentDigestHashAlgo());

        $i->setContentDigestAlgo('sha-512');
        self::assertTrue($i->hasContentDigestHashAlgo());
    }

    public function testWithCreated(): void
    {
        $i = new SigningConfiguration();
        self::assertNull($i->metadata->created);
        $dt = 123456;
        $i->setCreated($dt);
        self::assertSame($dt, $i->metadata->created);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidDerivedKeyException
     */
    public function testSignHeader(): void
    {
        $i = new SigningConfiguration();
        self::assertEmpty($i->signatureParams->getSignatureParameters());
        $i->signHeaders(['foo', 'bar']);
        self::assertSame(['foo', 'bar'], $i->signatureParams->getSignatureParameters());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     */
    public function testSetContentDigestHashAlgo(): void
    {
        $i = new SigningConfiguration();
        self::assertNull($i->contentDigestAlgo);
        $i->setContentDigestAlgo('sha-512');
        self::assertSame(DigestAlgorithmsEnum::sha512, $i->contentDigestAlgo);
    }

    public function testWithSignatureKeyName(): void
    {
        $i = new SigningConfiguration();
        self::assertNull($i->signatureKeyName);
        $i->setSignatureKeyName('keyname=');
        self::assertSame('keyname=', $i->signatureKeyName);
    }

    public function testWithExpires(): void
    {
        $i = new SigningConfiguration();
        self::assertNull($i->metadata->expires);
        $dt = 123456;
        $i->setExpires($dt);
        self::assertSame($dt, $i->metadata->expires);
    }

    public function testWithNonce(): void
    {
        $i = new SigningConfiguration();
        self::assertNull($i->metadata->nonce);
        $i->withNonce('foo');
        self::assertSame('foo', $i->metadata->nonce);
    }

    public function testWithKeyid(): void
    {
        $i = new SigningConfiguration();
        self::assertNull($i->metadata->keyid);
        $i->setKeyid('foo');
        self::assertSame('foo', $i->metadata->keyid);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public function testWithSigningAlgorithm(): void
    {
        $i = new SigningConfiguration();
        self::assertNull($i->metadata->alg);
        $i->setSigningAlgorithm(SigningEnum::rsa_pss_sha512);
        self::assertSame(SigningEnum::rsa_pss_sha512, $i->metadata->alg);
        $i->setSigningAlgorithm('ecdsa-p256-sha256');
        self::assertSame(SigningEnum::ecdsa_p_256_sha_256, $i->metadata->alg);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     */
    public function testGetContentDigestHashAlgoOrThrow(): void
    {
        $this->expectExceptionObject(
            new ContentDigestException('Hash algorithm "Missing" for the content-digest not supported'),
        );
        $i = new SigningConfiguration();
        $i->getContentDigestHashAlgoOrThrow();
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     */
    public function testGetContentDigestHashAlgoOrThrowValid(): void
    {
        $i = new SigningConfiguration();

        $i->setContentDigestAlgo('sha-512');
        self::assertSame(DigestAlgorithmsEnum::sha512, $i->getContentDigestHashAlgoOrThrow());
    }
}
