<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Extractors;

use Filefabrik\Rfc9421\Extractors\Components;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Components::class)]
final class ComponentsTest extends TestCase
{
    /**
     * @covers \Filefabrik\Rfc9421\Extractors\Components::toArray
     * @return void
     */
    public function testToArray(): void
    {
        $input = '"content-length" "@query-param" "example-dict"';

        $result = new Components($input)->toArray();
        $this->assertSame(['content-length', '@query-param', 'example-dict'], $result);
    }

}
