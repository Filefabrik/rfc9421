<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Signer;

use Filefabrik\Rfc9421\Exceptions\AlreadySetException;
use Filefabrik\Rfc9421\Exceptions\InvalidDerivedKeyException;
use Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException;
use Filefabrik\Rfc9421\Signer\SigCompilerHelper;
use Filefabrik\Rfc9421\Signer\SignatureParams;
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
            'someSig=("test" "@test" "try-out" "some-header")',
            SigCompilerHelper::makeSig1fromArray(['test', '@test', 'try-out', 'some-header'], 'someSig='),
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
            'sig1=("test" "@test" "try-out" "some-header")',
            new SigCompilerHelper($signatureParams, 'sig1=')->makeSig1fromSignatureParams(),
        );
    }
}
