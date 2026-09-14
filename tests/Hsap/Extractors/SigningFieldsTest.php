<?php

declare(strict_types=1);

namespace Filefabrik\HsapTest\Extractors;

use Filefabrik\Hsap\Extractors\SigningFields;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use UnexpectedValueException;

#[CoversClass(SigningFields::class)]
final class SigningFieldsTest extends TestCase
{
    /**
     * @covers \Filefabrik\Hsap\Extractors\SigningFields::toArray
     * @return void
     */
    public function testToArray(): void
    {
        $input = '("content-length" "@query-param" "example-dict")';

        $result = new SigningFields($input)->toArray();
        $this->assertSame(['content-length', '@query-param', 'example-dict'], $result);
    }

    /**
     * @covers \Filefabrik\Hsap\Extractors\SigningFields::toArray
     * @return void
     */
    public function testToArrayThrowsBraceStart(): void
    {
        $this->expectExceptionObject(new UnexpectedValueException('Unexpected start end of field.'));
        $input = '"content-length" "@query-param" "example-dict")';

        new SigningFields($input)->toArray();
    }

    /**
     * @covers \Filefabrik\Hsap\Extractors\SigningFields::toArray
     * @return void
     */
    public function testToArrayThrowsBraceEnd(): void
    {
        $this->expectExceptionObject(new UnexpectedValueException('Unexpected start end of field.'));
        $input = '("content-length" "@query-param" "example-dict"';

        new SigningFields($input)->toArray();
    }

    /**
     * @covers \Filefabrik\Hsap\Extractors\SigningFields::toArray
     * @return void
     */
    public function testToArrayThrowsBraceBoth(): void
    {
        $this->expectExceptionObject(new UnexpectedValueException('Unexpected start end of field.'));
        $input = '"content-length" "@query-param" "example-dict"';

        new SigningFields($input)->toArray();
    }

    /**
     * @covers \Filefabrik\Hsap\Extractors\SigningFields::toArray
     * @return void
     */
    public function testToArrayThrowsKeyname(): void
    {
        $this->expectExceptionObject(new UnexpectedValueException('Unexpected value: "content-length'));
        $input = '("content-length "@query-param" "example-dict")';

        new SigningFields($input)->toArray();
    }

    /**
     * @covers \Filefabrik\Hsap\Extractors\SigningFields::validateRemoveBraces
     * @return void
     * @throws \ReflectionException
     */
    public function testValidateRemoveBraces(): void
    {
        $input        =
            '("content-length" "@query-param";name="bat" "example-dict" "example-dict";sf "example-dict";key="a" "example-dict";key="d")';
        $exampleClass = new SigningFields($input);

        $reflection = new ReflectionClass($exampleClass);
        $method     = $reflection->getMethod('validateRemoveBraces');
        $method->setAccessible(true);

        $expect =
            '"content-length" "@query-param";name="bat" "example-dict" "example-dict";sf "example-dict";key="a" "example-dict";key="d"';
        $result = $method->invoke($exampleClass);
        $this->assertSame($expect, $result);
    }

}
