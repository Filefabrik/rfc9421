<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Support;

use Filefabrik\Rfc9421\Support\UnquoteHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

#[CoversClass(UnquoteHelper::class)]
final class UnquoteHelperTest extends TestCase
{
    public function testUnquote(): void
    {
        $input = '"content-length"';
        self::assertSame('content-length', UnquoteHelper::unquote($input));
    }

    public function testUnquoteMissingThrow(): void
    {
        $this->expectExceptionObject(
            new UnexpectedValueException(
                'The expected value has to be wrapped by quotes "value" but is content-length.',
            ),
        );
        $input = 'content-length';
        UnquoteHelper::unquote($input);
    }

    public function testUnquoteMissingStartThrow(): void
    {
        $this->expectExceptionObject(
            new UnexpectedValueException(
                'The expected value has to be wrapped by quotes "value" but is "content-length.',
            ),
        );
        $input = '"content-length';
        UnquoteHelper::unquote($input);
    }

    public function testUnquoteMissingEndThrow(): void
    {
        $this->expectExceptionObject(
            new UnexpectedValueException(
                'The expected value has to be wrapped by quotes "value" but is content-length".',
            ),
        );
        $input = 'content-length"';
        UnquoteHelper::unquote($input);
    }
}
