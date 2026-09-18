<?php

declare(strict_types=1);

namespace Filefabrik\HsapTest\Support;

use Filefabrik\Hsap\Support\UnquoteHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

#[CoversClass(UnquoteHelper::class)]
final class UnquoteHelperTest extends TestCase
{
    public function testUnquote(): void
    {
        $input = '"content-length"';
        $this->assertSame('content-length', UnquoteHelper::unquote($input));
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
