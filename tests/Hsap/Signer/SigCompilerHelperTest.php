<?php

declare(strict_types=1);

namespace Filefabrik\HsapTest\Signer;

use Filefabrik\Hsap\Exceptions\AlreadySetException;
use Filefabrik\Hsap\Exceptions\InvalidDerivedKeyException;
use Filefabrik\Hsap\Exceptions\InvalidHeaderKeyException;
use Filefabrik\Hsap\SignatureParams;
use Filefabrik\Hsap\Signer\SigCompilerHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SigCompilerHelper::class)]
final class SigCompilerHelperTest extends TestCase
{
    /**
     * @covers SigCompilerHelper::compileFromArray
     * @return void
     */
    public function testCompileFromArray(): void
    {
        $this->assertEquals(
            '"test" "@test" "try-out" "some-header"',
            SigCompilerHelper::compileFromArray(['test', '@test', 'try-out', 'some-header']),
        );
    }

    /**
     * @covers SigCompilerHelper::makeSig1fromArray
     * @return void
     */
    public function testMakeSig1fromArray(): void
    {
        $this->assertEquals(
            'sig1=("test" "@test" "try-out" "some-header");',
            SigCompilerHelper::makeSig1fromArray(['test', '@test', 'try-out', 'some-header']),
        );
    }

    /**
     * @covers SigCompilerHelper::compileFromSignatureParams
     * @throws InvalidHeaderKeyException
     * @throws AlreadySetException
     * @throws InvalidDerivedKeyException
     */
    public function testCompileFromSignatureParams(): void
    {
        $signatureParams = new SignatureParams()->setHeaderName('test')
                                                ->setDerivedComponentName('@test')
                                                ->setHeaderName('try-out')
                                                ->setHeaderName('some-header')
        ;

        $this->assertEquals(
            '"test" "@test" "try-out" "some-header"',
            SigCompilerHelper::compileFromSignatureParams($signatureParams),
        );
    }

    /**
     * @covers SigCompilerHelper::makeSig1fromSignatureParams
     * @throws InvalidHeaderKeyException
     * @throws AlreadySetException
     * @throws InvalidDerivedKeyException
     */
    public function testMakeSig1fromSignatureParams(): void
    {
        $signatureParams = new SignatureParams()->setHeaderName('test')
                                                ->setDerivedComponentName('@test')
                                                ->setHeaderName('try-out')
                                                ->setHeaderName('some-header')
        ;

        $this->assertEquals(
            'sig1=("test" "@test" "try-out" "some-header");',
            SigCompilerHelper::makeSig1fromSignatureParams($signatureParams),
        );
    }
}
