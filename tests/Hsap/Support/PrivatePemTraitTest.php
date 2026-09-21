<?php

declare(strict_types=1);

namespace Filefabrik\HsapTest\Support;

use Filefabrik\Hsap\Support\PrivatePemTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Filefabrik\Hsap\Support\PrivatePemTrait::class)]
final class PrivatePemTraitTest extends TestCase
{
    public function testGetPrivatePem(): void
    {
        $cl = new class () {
            use PrivatePemTrait;
        };
        $cl->setPrivatePem('pem');
        $this->assertSame('pem', $cl->getPrivatePem());
    }

}
