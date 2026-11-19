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

use Filefabrik\Rfc9421\Dto\ComponentItemDto;
use Filefabrik\Rfc9421\Exceptions\AlreadySetException;
use Filefabrik\Rfc9421\Exceptions\EnumTypeException;
use Filefabrik\Rfc9421\Signature\ComponentBuilder;
use Filefabrik\Rfc9421\Signature\ComponentName;
use Filefabrik\Rfc9421\SigningComponents;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SigningComponents::class)]
final class SigningComponentsTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
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
            new ComponentBuilder('@authority')->withBs()->toComponentItemDto()
                         ->getItem()
                         ->toHttpValue(),
        );

        self::assertEquals(
            '"@authority";bs',
            $i
                ->getComponents()[0]->getItem()
                                    ->toHttpValue(),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
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
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
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
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testSignHeaders(): void
    {
        $i = new SigningComponents();
        self::assertEmpty(
            $i->getComponents(),
        );
        $i->signHeaders(
            [
                new ComponentBuilder('Foo')->toComponentItemDto(),
                new ComponentBuilder('bar'),
                new ComponentBuilder('new')->withName('testname'),
            ],
        );

        self::assertEquals(
            [
                ComponentName::fromString('foo'),
                ComponentName::fromString('bar'),
                ComponentName::fromString('"new";name="testname"'),
            ],
            $i->getComponents(),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
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
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
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
            '"foo-with-req";sf;req',
            $i->getComponents()[1]->getHttpValue(),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
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
            '"foo-with-req";bs;req',
            $i->getComponents()[1]->getHttpValue(),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
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
            '"foo-with-req";key="the-key";req',
            $i->getComponents()[1]->getHttpValue(),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
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
            '"foo-with-req";name="the-key";req',
            $i->getComponents()[1]->getHttpValue(),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testSet(): void
    {
        $t = new SigningComponents()->sign('test')
                                    ->sign('test2', true)
        ;

        self::assertEquals([
            ComponentName::fromString('test'),
            ComponentItemDto::fromComponentBuilder(new ComponentBuilder('test2')->withReq()),

        ], $t->getComponents());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
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
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testFindComponentByName(): void
    {
        $t              = new SigningComponents()->sign('test')
                                                 ->sign('test', true)
        ;
        $current        = ComponentName::withComponent('test', true)
                                       ->getHttpValue()
        ;
        $foundComponent = $t->findComponentByName($current);
        self::assertEquals(
            '"test";req',
            $foundComponent->getHttpValue(),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testFindComponentByNameNotFound(): void
    {
        $t = new SigningComponents()->signHeader('test')
                                    ->sign('test2', true)
        ;

        self::assertNull(
            $t->findComponentByName(ComponentName::withComponent('test4', true)->fieldName),
        );
    }
}
