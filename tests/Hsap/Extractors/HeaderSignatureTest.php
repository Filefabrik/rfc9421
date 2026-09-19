<?php

declare(strict_types=1);

namespace Filefabrik\HsapTest\Extractors;

use Filefabrik\Hsap\Extractors\HeaderSignature;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(HeaderSignature::class)]
final class HeaderSignatureTest extends TestCase
{
    /**
     * @covers HeaderSignature::extractSignatureNameKey
     * @return void
     */
    public function testExtractSignatureName(): void
    {
        $headerKey               = 'testValue=:Gemgf5YIl0pbTUuU5gqaI+U4Br6SRCuODMwPFpHN08gPVqFQ67m0hBFmZu81s:';
        $headerSignatureInstance = new HeaderSignature($headerKey);
        $this->assertSame('testValue=', $headerSignatureInstance->extractSignatureNameKey());
    }

    /**
     * @covers HeaderSignature::extractSignatureNameKey
     * @return void
     */
    public function testExtractSignatureNameEmpty(): void
    {
        $headerKey               = ':Gemgf5YIl0pbTUuU5gqaI+U4Br6SRCuODMwPFpHN08gPVqFQ67m0hBFmZu81s:';
        $headerSignatureInstance = new HeaderSignature($headerKey);
        $this->assertSame('', $headerSignatureInstance->extractSignatureNameKey());
    }

    /**
     * @covers HeaderSignature::extractSignatureNameKey
     * @return void
     */
    public function testExtractSignatureNameNotFound(): void
    {
        $headerKey               = 'Gemgf5YIl0pbTUuU5gqaI+U4Br6SRCuODMwPFpHN08gPVqFQ67m0hBFmZu81s';
        $headerSignatureInstance = new HeaderSignature($headerKey);
        $this->assertNull($headerSignatureInstance->extractSignatureNameKey());
    }

    /**
     * @covers HeaderSignature::getSignatureValue
     * @return void
     */
    public function testGetSignatureValue(): void
    {
        $headerKey               = 'testValue=:Gemgf5YIl0pbTUuU5gqaI+U4Br6SRCuODMwPFpHN08gPVqFQ67m0hBFmZu81s:';
        $headerSignatureInstance = new HeaderSignature($headerKey);
        $this->assertSame(
            'Gemgf5YIl0pbTUuU5gqaI+U4Br6SRCuODMwPFpHN08gPVqFQ67m0hBFmZu81s',
            $headerSignatureInstance->getSignatureValue(),
        );
    }

    /**
     * @covers HeaderSignature::getSignatureValue
     * @return void
     */
    public function testGetSignatureValueEmpty(): void
    {
        $headerKey               = 'testValue=::';
        $headerSignatureInstance = new HeaderSignature($headerKey);
        $this->assertSame('', $headerSignatureInstance->getSignatureValue());
    }

    /**
     * @covers HeaderSignature::getSignatureValue
     * @return void
     */
    public function testGetSignatureValueNull(): void
    {
        $headerKey               = 'Gemgf5YIl0pbTUuU5gqaI+U4Br6SRCuODMwPFpHN08gPVqFQ67m0hBFmZu81s';
        $headerSignatureInstance = new HeaderSignature($headerKey);
        $this->assertNull($headerSignatureInstance->getSignatureValue());
    }

    
    public function testExtractSignatureString()
    {
        $headerKey               = 't:Gemgf5YIl0pbTUuU5gqaI+U4Br6SRCuODMwPFpHN08gPVqFQ67m0hBFmZu81s';
        $headerSignatureInstance = new HeaderSignature($headerKey);

        $s = $headerSignatureInstance->extractSignatureString();
        self::assertEquals([], $s);
    }
}
