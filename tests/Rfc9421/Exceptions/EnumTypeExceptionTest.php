<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * todo insert correct copyright
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Exceptions;

use Filefabrik\Rfc9421\Enums\InitialContentsEnum;
use Filefabrik\Rfc9421\Exceptions\EnumTypeException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EnumTypeException::class)]
final class EnumTypeExceptionTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public function testTypeError(): void
    {
        $this->expectExceptionObject(
            new EnumTypeException('"something" is not a valid enumeration of "'.InitialContentsEnum::class.'" Enum.'),
        );
        throw EnumTypeException::TypeError('something', InitialContentsEnum::class);
    }
}
