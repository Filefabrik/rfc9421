<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Signer;

use Filefabrik\Rfc9421\Signature\Components;
use Filefabrik\Rfc9421\Signer\SigCompilerHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SigCompilerHelper::class)]
final class SigCompilerHelperTest extends TestCase
{
    public function testMakeSig1fromArray(): void
    {
        $components = new Components()->adding('test')
                                      ->adding('@test')
                                      ->adding('try-out')
                                      ->adding('some-header')
        ;
        self::assertEquals(
            'someSig=("test" "@test" "try-out" "some-header")',
            SigCompilerHelper::makeSig1fromComponents($components, 'someSig='),
        );
    }

    public function testMakeSig1fromSignatureParams(): void
    {
        $components = new Components()->adding('test')
                                      ->adding('@test')
                                      ->adding('try-out')
                                      ->adding('some-header')
        ;

        self::assertEquals(
            'sig1=("test" "@test" "try-out" "some-header")',
            new SigCompilerHelper($components, 'sig1=')->makeSig1fromSignatureParams(),
        );
    }
}
