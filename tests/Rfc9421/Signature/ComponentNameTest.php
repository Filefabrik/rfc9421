<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Signature;

use Filefabrik\Rfc9421\Exceptions\EnumTypeException;
use Filefabrik\Rfc9421\Exceptions\SignatureExtractionException;
use Filefabrik\Rfc9421\Signature\ComponentName;
use Filefabrik\Rfc9421\Signature\DerivedComponentEnum;
use Filefabrik\Rfc9421\Signature\InitialContentsEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ComponentName::class)]
final class ComponentNameTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function test__construct(): void
    {
        // todo does not work with structured fields
        $i = ComponentName::fromComponentName('test');
        self::assertEquals(
            'test',
            $i->getItem()
              ->value(),
        );

        $i = ComponentName::fromComponentName('"test55"');
        self::assertEquals(
            'test55',
            $i->getItem()
              ->value(),
        );

        $i = ComponentName::fromComponentName('"test55"');
        self::assertEquals(
            'test55',
            $i->getItem()
              ->value(),
        );

        $i = ComponentName::fromNameAndInitialContent('caramba', InitialContentsEnum::name);
        self::assertEquals(
            'caramba',
            $i->getItem()
              ->value(),
        );
        self::assertEquals(
            '"caramba";name',
            $i->getItem()
              ->toHttpValue(),
        );

        // by string with @
        $i = ComponentName::fromNameAndInitialContent('@status', InitialContentsEnum::name);
        self::assertEquals(
            '@status',
            $i->getItem()
              ->value(),
        );

        $i = ComponentName::fromString('"test55";name');
        self::assertEquals(
            '"test55";name',
            $i->getItem()
              ->toHttpValue(),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function test__constructThrow(): void
    {
        $this->expectExceptionObject(
            new EnumTypeException(
                '"@test" is not a valid enumeration of "'.DerivedComponentEnum::class.'" Enum.',
            ),
        );
        $t = ComponentName::fromComponentName('@test');
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function test__constructThrowWithQuoted(): void
    {
        $this->expectExceptionObject(
            new EnumTypeException(
                '"@test" is not a valid enumeration of "'.DerivedComponentEnum::class.'" Enum.',
            ),
        );
        $t = ComponentName::fromComponentName('"@test"');
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testGetItemThrow(): void
    {
        $this->expectExceptionObject(
            new SignatureExtractionException(
                'The HTTP textual representation ""test55" for a String contains an invalid end string.',
            ),
        );
        ComponentName::fromString('"test55');
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testFromNameAndInitialContentEnumWithValue(): void
    {
        $i = ComponentName::fromNameAndInitialContentEnumWithValue('caramba', InitialContentsEnum::name, 'myName');
        self::assertEquals('"caramba";name="myName"', $i->httpValue);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testFromRegex4Segments(): void
    {
        $i = ComponentName::fromNameAndInitialContentEnumWithValue(
            'caramba',
            InitialContentsEnum::name,
            'myName',
        )->httpValue;
        self::assertSame('"caramba";name="myName"', ComponentName::fromString($i)->httpValue);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testFromRegexThrow(): void
    {
        $this->expectExceptionObject(new SignatureExtractionException(sprintf('Segment has unknown format ";"')));
        ComponentName::fromString(';');
    }
}
