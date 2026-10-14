<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Signer;

use Filefabrik\Rfc9421\Exceptions\EnumTypeException;
use Filefabrik\Rfc9421\Signature\Components;
use Filefabrik\Rfc9421\Signature\InitialContentsEnum;
use Filefabrik\Rfc9421\Signer\SigCompilerHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SigCompilerHelper::class)]
final class SigCompilerHelperTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     */
    public function testMakeSig1fromComponents(): void
    {
        $components = new Components()->setByName('test')
                                      ->setByName('@test')
                                      ->setByName('try-out')
                                      ->setByName('some-header')
        ;
        self::assertEquals(
            'someSig=("test" "@test" "try-out" "some-header")',
            SigCompilerHelper::makeSig1fromComponents($components, 'someSig='),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     */
    public function testMakeSig1fromSignatureParams(): void
    {
        $components = new Components()->setByName('test')
                                      ->setByName('@test')
                                      ->setByName('try-out')
                                      ->setByName('some-header')
        ;

        self::assertEquals(
            'sig1=("test" "@test" "try-out" "some-header")',
            new SigCompilerHelper($components, 'sig1=')->makeSig1fromSignatureParams(),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     */
    public function testMakeSig1fromConstructWithInitial(): void
    {
        $components = new Components()->setByName('test', 'req')
                                      ->setByName('@test')
                                      ->setByName('try-out')
                                      ->setByName('some-header')
        ;

        self::assertEquals(
            'sig1=("test";req "@test" "try-out" "some-header")',
            new SigCompilerHelper($components, 'sig1=')->makeSig1fromSignatureParams(),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     */
    public function testMakeSig1fromConstructThrows(): void
    {
        $this->expectExceptionObject(EnumTypeException::TypeError('baddabum', InitialContentsEnum::class));
        $components = new Components()->setByName('test', 'baddabum')
                                      ->setByName('@test')
                                      ->setByName('try-out')
                                      ->setByName('some-header')
        ;
        new SigCompilerHelper($components, 'sig1=')->makeSig1fromSignatureParams();
    }
}
