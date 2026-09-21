<?php

declare(strict_types=1);

namespace Filefabrik\HsapTest\Support;

use Filefabrik\Hsap\Support\PublicPemTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Filefabrik\Hsap\Support\PublicPemTrait::class)]
final class PublicPemTraitTest extends TestCase
{
    public function testGetPublicPem(): void
    {
        $cl = new class () {
            use PublicPemTrait;
        };
        $cl->setPublicPem('pem');
        $this->assertSame('pem', $cl->getPublicPem());
    }
}
