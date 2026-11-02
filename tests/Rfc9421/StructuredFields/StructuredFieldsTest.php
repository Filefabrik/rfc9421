<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\StructuredFields;

use Filefabrik\Rfc9421\StructuredFields\StructuredFields;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

#[CoversClass(StructuredFields::class)]
final class StructuredFieldsTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     */
    public function testGetDefaultList(): void
    {
        self::assertCount(66, StructuredFields::getDefaultList());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     */
    public function testFromDefaultList(): void
    {
        $instance = StructuredFields::fromDefaultList();
        self::assertInstanceOf(StructuredFields::class, $instance);
        self::assertCount(66, $instance->fields);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     */
    public function testGetOrNull(): void
    {
        $instance = StructuredFields::fromDefaultList();
        self::assertNull($instance->getOrNull('some'));
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

    public function testGetOrThrow(): void
    {
        $this->expectExceptionObject(new UnexpectedValueException('Field "some" does not exist.'));
        $instance = StructuredFields::fromDefaultList();
        self::assertNull($instance->getOrThrow('some'));
    }
}
