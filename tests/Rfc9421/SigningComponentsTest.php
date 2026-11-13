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

use Filefabrik\Rfc9421\Exceptions\AlreadySetException;
use Filefabrik\Rfc9421\Exceptions\EnumTypeException;
use Filefabrik\Rfc9421\Signature\ComponentName;
use Filefabrik\Rfc9421\SigningComponents;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SigningComponents::class)]
final class SigningComponentsTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testSignHeader(): void
    {
        $i = new SigningComponents();
        self::assertEmpty(
            $i->getComponents(),
        );
        $i->signHeader('foo');

        $c = $i->getComponents()[0]->getItem()
                                   ->toHttpValue()
        ;
        self::assertEquals('"foo"', $c);

        $i = new SigningComponents();
        $i->signHeader(
            ComponentName::fromNameAndInitialContent('@authority', 'name')
                         ->getItem()
                         ->toHttpValue(),
        );

        self::assertEquals(
            '"@authority";name',
            $i
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
        $i = new SigningComponents();
        self::assertEmpty(
            $i->getComponents(),
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
        $i = new SigningComponents();
        self::assertEmpty(
            $i->getComponents(),
        );
        $i->signHeader('@bullshit');
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testSignHeaders(): void
    {
        $i = new SigningComponents();
        self::assertEmpty(
            $i->getComponents(),
        );
        $i->signHeaders(['Foo', 'bar', ComponentName::fromNameAndInitialContent('new', 'key')]);

        self::assertEquals(
            [
                ComponentName::fromComponentName('foo'),
                ComponentName::fromComponentName('bar'),
                ComponentName::fromNameAndInitialContent('new', 'key'),
            ],
            $i->getComponents(),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testSignComponent(): void
    {
        $i = new SigningComponents();
        self::assertEmpty(
            $i->getComponents(),
        );
        $i->sign('foo');

        $c = $i->getComponents()[0]->getHttpValue();
        self::assertEquals('"foo"', $c);

        $i->sign('foo-with-req', true);
        self::assertEquals(
            '"foo-with-req";req',
            $i->getComponents()[1]->getHttpValue(),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testSignComponentSf(): void
    {
        $i = new SigningComponents();
        self::assertEmpty(
            $i->getComponents(),
        );
        $i->signWithSF('foo');

        $c = $i->getComponents()[0]->getHttpValue();
        self::assertEquals('"foo";sf', $c);

        $i->signWithSF('foo-with-req', true);
        self::assertEquals(
            '"foo-with-req";req;sf',
            $i->getComponents()[1]->getHttpValue(),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testSignComponentBS(): void
    {
        $i = new SigningComponents();
        self::assertEmpty(
            $i->getComponents(),
        );
        $i->signWithBS('foo');

        $c = $i->getComponents()[0]->getHttpValue();
        self::assertEquals('"foo";bs', $c);

        $i->signWithBS('foo-with-req', true);
        self::assertEquals(
            '"foo-with-req";req;bs',
            $i->getComponents()[1]->getHttpValue(),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testSignComponentKey(): void
    {
        $i = new SigningComponents();
        self::assertEmpty(
            $i->getComponents(),
        );
        $i->signWithKey('foo', 'the-key');

        $c = $i->getComponents()[0]->getHttpValue();
        self::assertEquals('"foo";key="the-key"', $c);

        $i->signWithKey('foo-with-req', 'the-key', true);
        self::assertEquals(
            '"foo-with-req";req;key="the-key"',
            $i->getComponents()[1]->getHttpValue(),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testSignComponentName(): void
    {
        $i = new SigningComponents();
        self::assertEmpty(
            $i->getComponents(),
        );
        $i->signWithName('foo', 'the-key');

        $c = $i->getComponents()[0]->getHttpValue();
        self::assertEquals('"foo";name="the-key"', $c);

        $i->signWithName('foo-with-req', 'the-key', true);
        self::assertEquals(
            '"foo-with-req";req;name="the-key"',
            $i->getComponents()[1]->getHttpValue(),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testSet(): void
    {
        $t = new SigningComponents()->sign('test')
                                    ->sign('test2', true)
        ;

        self::assertEquals([
            ComponentName::fromComponentName('test'),
            ComponentName::fromNameAndInitialContent('test2', 'req'),
        ], $t->getComponents());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testSetThrow(): void
    {
        $this->expectExceptionObject(new AlreadySetException('The Component "test2" already exists.'));
        new SigningComponents()->sign('test')
                               ->sign('test2')
                               ->sign('test2')
        ;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testFindComponentByName(): void
    {
        $t = new SigningComponents()->sign('test')
                                    ->sign('test2', true)
        ;

        self::assertEquals(
            ComponentName::fromNameAndInitialContent('test2', 'req'),
            $t->findComponentByName(ComponentName::fromNameAndInitialContent('test2', 'req')->fieldName),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testFindComponentByNameNotFound(): void
    {
        $t = new SigningComponents()->signHeader('test')
                                    ->sign('test2', true)
        ;

        self::assertNull(
            $t->findComponentByName(ComponentName::fromNameAndInitialContent('test4', 'req')->fieldName),
        );
    }
}
