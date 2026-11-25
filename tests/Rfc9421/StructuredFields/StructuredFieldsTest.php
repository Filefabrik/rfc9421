<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\StructuredFields;

use Filefabrik\Rfc9421\Exceptions\StructuredFieldException;
use Filefabrik\Rfc9421\StructuredFields\StructuredFields;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StructuredFields::class)]
final class StructuredFieldsTest extends TestCase
{
    public function testGetDefaultList(): void
    {
        self::assertCount(66, StructuredFields::getDefaultList());
    }

    public function testFromDefaultList(): void
    {
        $instance = StructuredFields::fromDefaultList();

        self::assertCount(66, $instance->fields);
    }

    public function testGetOrNull(): void
    {
        $instance = StructuredFields::fromDefaultList();
        self::assertNull($instance->getOrNull('some'));

        self::assertSame('list', $instance->getOrNull(' x-XSS-protection '));
    }

    public function testAdd(): void
    {
        $instance = new StructuredFields();
        self::assertCount(0, $instance->fields);
        $instance->add('TeSt', 'me');
        self::assertCount(1, $instance->fields);
        $item = $instance->getOrNull('TesT');
        self::assertSame('me', $item);
    }

    public function testAddMany(): void
    {
        $instance = new StructuredFields()->addMany(['Test' => 'me', 'neGGst' => 'some']);
        self::assertSame(['test' => 'me', 'neggst' => 'some'], $instance->fields);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     */
    public function testGetOrThrowExits(): void
    {
        $instance = StructuredFields::fromDefaultList();
        self::assertSame('list', $instance->getOrThrow(' x-XSS-protection '));
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     */
    public function testGetOrThrow(): void
    {
        $this->expectExceptionObject(
            new StructuredFieldException('The field "some" is not mapped into StructuredFields.'),
        );
        StructuredFields::fromDefaultList()
                        ->getOrThrow('some')
        ;
    }

    public function testNormalizeKey(): void
    {
        $instance = StructuredFields::fromDefaultList();
        self::assertSame('list', $instance->getOrNull('x-xss-protection'));
        self::assertSame('list', $instance->getOrNull(' x-xss-protection '));

        self::assertSame('list', $instance->getOrNull(' x-XSS-protection '));
    }
}
