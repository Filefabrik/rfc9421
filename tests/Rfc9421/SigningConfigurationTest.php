<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test;

use Filefabrik\Rfc9421\Enums\DigestAlgorithmsEnum;
use Filefabrik\Rfc9421\Enums\SigningEnum;
use Filefabrik\Rfc9421\Exceptions\ContentDigestException;
use Filefabrik\Rfc9421\Exceptions\EmptyException;
use Filefabrik\Rfc9421\SigningComponents;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421\StructuredFields\MethodsMapper;
use Filefabrik\Rfc9421\StructuredFields\StructuredFields;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SigningConfiguration::class)]
final class SigningConfigurationTest extends TestCase
{
    public function testWithTag(): void
    {
        $i = new SigningConfiguration();
        self::assertSame(
            'tagname',
            $i->setTag('tagname')
              ->getMetadata()->tag,
        );
        $i->setTag(null);
        self::assertNull($i->getMetadata()->tag);
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
        self::assertNull(
            $i->getMetadata()
              ->getCreated(),
        );
        $dt = 123456;
        $i->setCreated($dt);
        self::assertSame(
            $dt,
            $i->getMetadata()
              ->getCreated(),
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
        self::assertNull($i->getSignatureKeyName());
        $i->setSignatureKeyName('keyname=');
        self::assertSame('keyname=', $i->getSignatureKeyName());
    }

    public function testWithExpires(): void
    {
        $i = new SigningConfiguration();
        self::assertNull($i->getMetadata()->expires);
        $dt = 123456;
        $i->setExpires($dt);
        self::assertSame($dt, $i->getMetadata()->expires);
    }

    public function testWithNonce(): void
    {
        $i = new SigningConfiguration();
        self::assertNull($i->getMetadata()->nonce);
        $i->withNonce('foo');
        self::assertSame('foo', $i->getMetadata()->nonce);
    }

    public function testWithKeyid(): void
    {
        $i = new SigningConfiguration();
        self::assertNull($i->getMetadata()->keyid);
        $i->setKeyid('foo');
        self::assertSame('foo', $i->getMetadata()->keyid);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public function testWithSigningAlgorithm(): void
    {
        $i = new SigningConfiguration();
        self::assertNull($i->getMetadata()->alg);
        $i->setSigningAlgorithm(SigningEnum::rsa_pss_sha512);
        self::assertSame(SigningEnum::rsa_pss_sha512, $i->getMetadata()->alg);
        $i->setSigningAlgorithm('ecdsa-p256-sha256');
        self::assertSame(SigningEnum::ecdsa_p_256_sha_256, $i->getMetadata()->alg);
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
        $c = new SigningComponents();
        $i = new SigningConfiguration();
        $i->setSigningComponents($c);
        self::assertSame($c, $i->getSigningComponents());
    }

    public function testGetStructuredFields(): void
    {
        $i = new SigningConfiguration();
        self::assertEquals(StructuredFields::getDefaultList(), $i->getStructuredFields()->fields);
    }

    public function testMethodsMapper(): void
    {
        $i = new SigningConfiguration();

        foreach (MethodsMapper::getDefaultMapper()->methods as $cnt => $item) {
            self::assertEquals($item->method, $i->getMethodsMapper()->methods[$cnt]->method);
        }
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     */
    public function testMustCreateContentDigest(): void
    {
        $i = new SigningConfiguration();

        //$instance = new GenerateContentDigestFaked();
        self::assertFalse($i->isMustGenerateContentDigest());
        $i->withContentDigest(DigestAlgorithmsEnum::sha512);
        self::assertTrue($i->isMustGenerateContentDigest());
    }

    public function testAddSigningMethod(): void
    {
        $i = new SigningConfiguration();
        self::assertCount(11, $i->getSigningMethodsWithDefaults()->methods);
        $i->addSigningMethod('foo', fn (): string => 'play');
        self::assertCount(12, $i->getSigningMethodsWithDefaults()->methods);
        $i->addSigningMethod('foo', fn (): string => 'play');
        self::assertCount(12, $i->getSigningMethodsWithDefaults()->methods);
    }

    public function testGetSigningMethodsWithDefaults(): void
    {
        $i = new SigningConfiguration();
        self::assertCount(11, $i->getSigningMethodsWithDefaults()->methods);
    }

    public function testAddSigningMethods(): void
    {
        $i = new SigningConfiguration();
        $i->addSigningMethods(
            [
                'foo' => fn (): string => 'play foo',
                'bar' => fn (): string => 'bar play',
                'baz' => fn (): string => 'play baz',
            ],
        );
        self::assertCount(14, $i->getSigningMethodsWithDefaults()->methods);
    }

    public function testSetPrivatePem(): void
    {
        $i = new SigningConfiguration();
        $i->setPrivatePem('foo');
        self::assertSame('foo', $i->getPrivatePem());
    }

    public function testGetPrivatePem(): void
    {
        $i = new SigningConfiguration();
        self::assertNull($i->getPrivatePem());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EmptyException
     */
    public function testGetPrivatePemOrThrowHas(): void
    {
        $i = new SigningConfiguration()->setPrivatePem('shiddy private');

        self::assertSame('shiddy private', $i->getPrivatePemOrThrow());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EmptyException
     */
    public function testGetPrivatePemOrThrowNull(): void
    {
        $this->expectExceptionObject(EmptyException::privatePemIsEmpty());
        $i = new SigningConfiguration();

        $i->getPrivatePemOrThrow();
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EmptyException
     */
    public function testGetPrivatePemOrThrowEmpty(): void
    {
        $this->expectExceptionObject(EmptyException::privatePemIsEmpty());
        $i = new SigningConfiguration();
        $i->setPrivatePem('');
        $i->getPrivatePemOrThrow();
    }

    public function testSetPublicPem(): void
    {
        $i = new SigningConfiguration();
        $i->setPublicPem('foo');
        self::assertSame('foo', $i->getPublicPem());
    }

    public function testGetPublicPem(): void
    {
        $i = new SigningConfiguration();
        self::assertNull($i->getPublicPem());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EmptyException
     */
    public function testGetPublicPemOrThrowHas(): void
    {
        $i = new SigningConfiguration()->setPublicPem('shiddy content');

        self::assertSame('shiddy content', $i->getPublicPemOrThrow());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EmptyException
     */
    public function testGetPublicPemOrThrowNull(): void
    {
        $this->expectExceptionObject(EmptyException::publicPemIsEmpty());
        $i = new SigningConfiguration();

        $i->getPublicPemOrThrow();
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EmptyException
     */
    public function testGetPublicPemOrThrowEmpty(): void
    {
        $this->expectExceptionObject(EmptyException::publicPemIsEmpty());
        $i = new SigningConfiguration();
        $i->setPublicPem('');
        $i->getPublicPemOrThrow();
    }


}
