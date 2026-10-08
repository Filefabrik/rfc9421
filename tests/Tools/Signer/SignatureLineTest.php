<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Tools\Signer;

use Filefabrik\Rfc9421\Signer\SignatureLine;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SignatureLine::class)]
class SignatureLineTest extends TestCase
{

    public function test__construct(): void
    {
        $i = new SignatureLine('one', 'val');
        self::assertEquals('one', $i->key);
        self::assertEquals('val', $i->value);
    }
}
