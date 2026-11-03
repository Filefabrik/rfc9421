<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Bakame;

use Filefabrik\Rfc9421\Bakame\Dictionary;
use Filefabrik\Rfc9421\Exceptions\StructuredFieldException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Dictionary::class)]
final class DictionaryTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     */
    public function testFromHttpValue(): void
    {
        $res = Dictionary::fromHttpValue('  a=1,    b=2;x=1;y=2,   c=(a   b   c), d');
        self::assertSame('a=1, b=2;x=1;y=2, c=(a b c), d', $res->toHttpValue());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     */
    public function testFromHttpValueThrows(): void
    {
        $this->expectExceptionObject(
            new StructuredFieldException('The HTTP textual representation is missing an excepted comma.'),
        );

        Dictionary::fromHttpValue('  a=1,    b=2;x=1;y=2,   c=(a   b   c), d ###;;shit');
    }
}
