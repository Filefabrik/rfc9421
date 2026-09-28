<?php

declare(strict_types=1);

namespace Filefabrik\HsapTest;

use Filefabrik\Hsap\Cryptography\SigningEnum;
use Filefabrik\Hsap\SigningConfiguration;
use Filefabrik\Hsap\Support\DigestAlgorithmsEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SigningConfiguration::class)]
final class SigningConfigurationTest extends TestCase
{
    public function testWithTag(): void
    {
        $i = new SigningConfiguration();
        $this->assertSame('tagname', $i->withTag('tagname')->metadata->tag);
        $i->withTag(null);
        $this->assertNull($i->metadata->tag);
    }

    public function testMustSignBody(): void
    {
        $i = new SigningConfiguration();
        $this->assertTrue($i->mustSignBody);
        $i->mustSignBody(false);
        $this->assertFalse($i->mustSignBody);
    }

    public function testGetContentDigestHashAlgo(): void
    {
        $i = new SigningConfiguration();
        $this->assertNull($i->contentDigestHashAlgo);
        $this->assertSame(DigestAlgorithmsEnum::sha256, $i->getContentDigestHashAlgo());
        $i->setContentDigestHashAlgo('sha-512');
        $this->assertSame(DigestAlgorithmsEnum::sha512, $i->contentDigestHashAlgo);
    }

    public function testWithCreated(): void
    {
        $i = new SigningConfiguration();
        $this->assertNull($i->metadata->created);
        $dt = 123456;
        $i->withCreated($dt);
        $this->assertSame($dt, $i->metadata->created);
    }

    public function testWithHeader(): void
    {
        $i = new SigningConfiguration();
        $this->assertEmpty($i->signatureParams->getSignatureParameters());
        $i->withHeader('foo', 'bar');
        $this->assertSame(['foo', 'bar'], $i->signatureParams->getSignatureParameters());
    }

    public function testSetContentDigestHashAlgo(): void
    {
        $i = new SigningConfiguration();
        $this->assertNull($i->contentDigestHashAlgo);
        $i->setContentDigestHashAlgo('sha-512');
        $this->assertSame(DigestAlgorithmsEnum::sha512, $i->contentDigestHashAlgo);
    }

    public function testWithSignatureKeyName(): void
    {
        $i = new SigningConfiguration();
        $this->assertNull($i->signatureKeyName);
        $i->withSignatureKeyName('keyname=');
        $this->assertSame('keyname=', $i->signatureKeyName);
    }

    public function testWithExpires(): void
    {
        $i = new SigningConfiguration();
        $this->assertNull($i->metadata->expires);
        $dt = 123456;
        $i->withExpires($dt);
        $this->assertSame($dt, $i->metadata->expires);
    }

    public function testWithNonce(): void
    {
        $i = new SigningConfiguration();
        $this->assertNull($i->metadata->nonce);
        $i->withNonce('foo');
        $this->assertSame('foo', $i->metadata->nonce);
    }

    public function testWithKeyid(): void
    {
        $i = new SigningConfiguration();
        $this->assertNull($i->metadata->keyid);
        $i->withKeyid('foo');
        $this->assertSame('foo', $i->metadata->keyid);
    }

    public function testWithSigningAlgorithm(): void
    {
        $i = new SigningConfiguration();
        $this->assertNull($i->metadata->alg);
        $i->withSigningAlgorithm(SigningEnum::ecdsa_p_512_sha_512);
        $this->assertSame(SigningEnum::ecdsa_p_512_sha_512, $i->metadata->alg);
        $i->withSigningAlgorithm('ecdsa-p256-sha256');
        $this->assertSame(SigningEnum::ecdsa_p_256_sha_256, $i->metadata->alg);
    }
}
