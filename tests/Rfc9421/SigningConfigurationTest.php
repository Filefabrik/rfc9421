<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test;

use Filefabrik\Rfc9421\Cryptography\SigningEnum;
use Filefabrik\Rfc9421\Exceptions\ContentDigestException;
use Filefabrik\Rfc9421\Exceptions\EnumTypeException;
use Filefabrik\Rfc9421\Signature\Component;
use Filefabrik\Rfc9421\Signature\Components;
use Filefabrik\Rfc9421\Signature\DerivedComponentEnum;
use Filefabrik\Rfc9421\Signature\InitialContentsEnum;
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
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     */
    public function testSignHeader(): void
    {
        $i = new SigningConfiguration();
        self::assertEmpty($i->signatureComponents->getComponents());
        $i->signHeader('foo', 'key');
        self::assertEquals([new Component('foo', InitialContentsEnum::key)], $i->signatureComponents->getComponents());

        $i = new SigningConfiguration();
        $i->signHeader(new Component('@authority', 'name'));

        self::assertEquals(
            [new Component(DerivedComponentEnum::authority, InitialContentsEnum::name)],
            $i->signatureComponents->getComponents(),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     */
    public function testSignHeaderThrowWrongKey(): void
    {
        $this->expectExceptionObject(
            new EnumTypeException(
                '"@foo" is not a valid enumeration of "Filefabrik\Rfc9421\Signature\DerivedComponentEnum" Enum.',
            ),
        );
        $i = new SigningConfiguration();
        self::assertEmpty($i->signatureComponents->getComponents());
        $i->signHeader('@foo');
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     */
    public function testSignHeaderThrowWrongInitialContent(): void
    {
        $this->expectExceptionObject(
            new EnumTypeException(
                '"bullshit" is not a valid enumeration of "Filefabrik\Rfc9421\Signature\InitialContentsEnum" Enum.',
            ),
        );
        $i = new SigningConfiguration();
        self::assertEmpty($i->signatureComponents->getComponents());
        $i->signHeader('@authority','bullshit');
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     */
    public function testSignHeaders(): void
    {
        $i = new SigningConfiguration();
        self::assertEmpty($i->signatureComponents->getComponents());
        $i->signHeaders(['foo', 'bar']);
        self::assertEquals([new Component('foo'), new Component('bar')], $i->signatureComponents->getComponents());

        $i = new SigningConfiguration();
        $i->signHeaders([DerivedComponentEnum::authority, 'bar']);
        self::assertEquals(
            [new Component('@authority'), new Component('bar')],
            $i->signatureComponents->getComponents(),
        );
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

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     */
    public function testSetContentDigestHashAlgoWithNull(): void
    {
        $i = new SigningConfiguration();
        self::assertNull($i->contentDigestAlgo);
        $i->setContentDigestAlgo('sha-512');
        self::assertSame(DigestAlgorithmsEnum::sha512, $i->contentDigestAlgo);
        $i->setContentDigestAlgo(null);
        self::assertNull($i->contentDigestAlgo);
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

    public function testSetComponents(): void
    {
        $c = new Components();
        $i = new SigningConfiguration();
        $i->setComponents($c);
        self::assertSame($c, $i->signatureComponents);
    }
}
