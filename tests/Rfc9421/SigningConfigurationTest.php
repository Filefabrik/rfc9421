<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test;

use Filefabrik\Rfc9421\Cryptography\SigningEnum;
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
        self::assertSame('tagname', $i->withTag('tagname')->metadata->tag);
        $i->withTag(null);
        self::assertNull($i->metadata->tag);
    }


    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     */
    public function testGetContentDigestHashAlgo(): void
    {
        $i = new SigningConfiguration();
        self::assertNull($i->contentDigestHashAlgo);
        self::assertNull( $i->getContentDigestHashAlgo());
        $i->setContentDigestHashAlgo('sha-512');
        self::assertSame(DigestAlgorithmsEnum::sha512, $i->contentDigestHashAlgo);
    }

    public function testWithCreated(): void
    {
        $i = new SigningConfiguration();
        self::assertNull($i->metadata->created);
        $dt = 123456;
        $i->withCreated($dt);
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
        $i->signHeader('foo', 'bar');
        self::assertSame(['foo', 'bar'], $i->signatureParams->getSignatureParameters());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     */
    public function testSetContentDigestHashAlgo(): void
    {
        $i = new SigningConfiguration();
        self::assertNull($i->contentDigestHashAlgo);
        $i->setContentDigestHashAlgo('sha-512');
        self::assertSame(DigestAlgorithmsEnum::sha512, $i->contentDigestHashAlgo);
    }

    public function testWithSignatureKeyName(): void
    {
        $i = new SigningConfiguration();
        self::assertNull($i->signatureKeyName);
        $i->withSignatureKeyName('keyname=');
        self::assertSame('keyname=', $i->signatureKeyName);
    }

    public function testWithExpires(): void
    {
        $i = new SigningConfiguration();
        self::assertNull($i->metadata->expires);
        $dt = 123456;
        $i->withExpires($dt);
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
        $i->withKeyid('foo');
        self::assertSame('foo', $i->metadata->keyid);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public function testWithSigningAlgorithm(): void
    {
        $i = new SigningConfiguration();
        self::assertNull($i->metadata->alg);
        $i->withSigningAlgorithm(SigningEnum::rsa_pss_sha512);
        self::assertSame(SigningEnum::rsa_pss_sha512, $i->metadata->alg);
        $i->withSigningAlgorithm('ecdsa-p256-sha256');
        self::assertSame(SigningEnum::ecdsa_p_256_sha_256, $i->metadata->alg);
    }
}
