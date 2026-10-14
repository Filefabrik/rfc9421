<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Exceptions;

use Filefabrik\Rfc9421\Exceptions\EnumTypeException;
use Filefabrik\Rfc9421\Signature\InitialContentsEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EnumTypeException::class)]
final class EnumTypeExceptionTest extends TestCase
{
    public function testTypeError(): void
    {
        $this->expectExceptionObject(
            new EnumTypeException('"something" is not a valid enumeration of "'.InitialContentsEnum::class.'" Enum.'),
        );
        throw EnumTypeException::TypeError('something',InitialContentsEnum::class);
    }
}
