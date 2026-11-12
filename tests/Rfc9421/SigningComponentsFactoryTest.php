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

use Filefabrik\Rfc9421\Exceptions\EnumTypeException;
use Filefabrik\Rfc9421\Signature\ComponentName;
use Filefabrik\Rfc9421\SigningComponentsFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SigningComponentsFactory::class)]
final class SigningComponentsFactoryTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testSignHeader(): void
    {
        $i = new SigningComponentsFactory();
        self::assertEmpty(
            $i->getSignatureComponents()
              ->getComponents(),
        );
        $i->signHeader('foo');

        $c = $i->getSignatureComponents()
               ->getComponents()[0]->getItem()
                                   ->toHttpValue()
        ;
        self::assertEquals('"foo"', $c);

        $i = new SigningComponentsFactory();
        $i->signHeader(
            ComponentName::fromNameAndInitialContent('@authority', 'name')
                         ->getItem()
                         ->toHttpValue(),
        );

        self::assertEquals(
            '"@authority";name',
            $i->getSignatureComponents()
              ->getComponents()[0]->getItem()
                                  ->toHttpValue(),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testSignHeaderThrowWrongKey(): void
    {
        $this->expectExceptionObject(
            new EnumTypeException(
                '"@foo" is not a valid enumeration of "Filefabrik\Rfc9421\Enums\DerivedComponentEnum" Enum.',
            ),
        );
        $i = new SigningComponentsFactory();
        self::assertEmpty(
            $i->getSignatureComponents()
              ->getComponents(),
        );
        $i->signHeader('@foo');
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testSignHeaderThrowWrongInitialContent(): void
    {
        $this->expectExceptionObject(
            new EnumTypeException(
                '"@bullshit" is not a valid enumeration of "Filefabrik\Rfc9421\Enums\DerivedComponentEnum" Enum.',
            ),
        );
        $i = new SigningComponentsFactory();
        self::assertEmpty(
            $i->getSignatureComponents()
              ->getComponents(),
        );
        $i->signHeader('@bullshit');
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testSignHeaders(): void
    {
        $i = new SigningComponentsFactory();
        self::assertEmpty(
            $i->getSignatureComponents()
              ->getComponents(),
        );
        $i->signHeaders(['Foo', 'bar', ComponentName::fromNameAndInitialContent('new', 'key')]);

        self::assertEquals(
            [
                ComponentName::fromComponentName('foo'),
                ComponentName::fromComponentName('bar'),
                ComponentName::fromNameAndInitialContent('new', 'key'),
            ],
            $i->getSignatureComponents()
              ->getComponents(),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testSignComponent(): void
    {
        $i = new SigningComponentsFactory();
        self::assertEmpty(
            $i->getSignatureComponents()
              ->getComponents(),
        );
        $i->signComponent('foo');

        $c = $i->getSignatureComponents()
               ->getComponents()[0]->getHttpValue();
        self::assertEquals('"foo"', $c);

        $i->signComponent('foo-with-req', true);
        self::assertEquals(
            '"foo-with-req";req',
            $i->getSignatureComponents()
              ->getComponents()[1]->getHttpValue(),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testSignComponentSf(): void
    {
        $i = new SigningComponentsFactory();
        self::assertEmpty(
            $i->getSignatureComponents()
              ->getComponents(),
        );
        $i->signComponentSf('foo');

        $c = $i->getSignatureComponents()
               ->getComponents()[0]->getHttpValue();
        self::assertEquals('"foo";sf', $c);

        $i->signComponentSf('foo-with-req', true);
        self::assertEquals(
            '"foo-with-req";req;sf',
            $i->getSignatureComponents()
              ->getComponents()[1]->getHttpValue(),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testSignComponentKey(): void
    {
        $i = new SigningComponentsFactory();
        self::assertEmpty(
            $i->getSignatureComponents()
              ->getComponents(),
        );
        $i->signComponentKey('foo', 'the-key');

        $c = $i->getSignatureComponents()
               ->getComponents()[0]->getHttpValue();
        self::assertEquals('"foo";key="the-key"', $c);

        $i->signComponentKey('foo-with-req', 'the-key', true);
        self::assertEquals(
            '"foo-with-req";req;key="the-key"',
            $i->getSignatureComponents()
              ->getComponents()[1]->getHttpValue(),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testSignComponentName(): void
    {
        $i = new SigningComponentsFactory();
        self::assertEmpty(
            $i->getSignatureComponents()
              ->getComponents(),
        );
        $i->signComponentName('foo', 'the-key');

        $c = $i->getSignatureComponents()
               ->getComponents()[0]->getHttpValue();
        self::assertEquals('"foo";name="the-key"', $c);

        $i->signComponentName('foo-with-req', 'the-key', true);
        self::assertEquals(
            '"foo-with-req";req;name="the-key"',
            $i->getSignatureComponents()
              ->getComponents()[1]->getHttpValue(),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testSignHeaderInitialContent(): void
    {
        $i = new SigningComponentsFactory();
        self::assertEmpty(
            $i->getSignatureComponents()
              ->getComponents(),
        );
        $i->signHeaderNameInitialContent('foo', 'key');

        self::assertEquals(
            [ComponentName::fromNameAndInitialContent('foo', 'key')],
            $i->getSignatureComponents()
              ->getComponents(),
        );
    }
}
