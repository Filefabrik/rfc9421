<?php

declare(strict_types=1);

namespace Filefabrik\HsapTest\Extractors;

use Filefabrik\Hsap\Exceptions\SignatureExtractionException;
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
        $this->expectExceptionObject(
            new SignatureExtractionException('Can not extract signature parts. Needs 3 segments but has 0'),
        );
        $headerKey = 'Gemgf5YIl0pbTUuU5gqaI+U4Br6SRCuODMwPFpHN08gPVqFQ67m0hBFmZu81s';
        new HeaderSignature($headerKey)->extractSignatureNameKey();
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
    public function testGetSignatureValueThrow(): void
    {
        $this->expectExceptionObject(
            new SignatureExtractionException('Can not extract signature parts. Needs 3 segments but has 0'),
        );
        $headerKey = 'Gemgf5YIl0pbTUuU5gqaI+U4Br6SRCuODMwPFpHN08gPVqFQ67m0hBFmZu81s';
        new HeaderSignature($headerKey)->getSignatureValue();
    }

    public function testExtractSignatureString(): void
    {
        $headerKey               = 't:Gemgf5YIl0pbTUuU5gqaI+U4Br6SRCuODMwPFpHN08gPVqFQ67m0hBFmZu81s';
        $headerSignatureInstance = new HeaderSignature($headerKey);

        $s = $headerSignatureInstance->extractSignatureString();
        self::assertEquals([], $s);
    }

    public function testGetBinarySignatureValue(): void
    {
        //todo not clear, test deeper
        // hmac 256
        $headerKey = 'testValue=:Q94UwavaMSqUpe1f1G5cmDTXj9wQYFp39lmKZ9YPUv4=:';

        $baseEncoded             = base64_decode('Q94UwavaMSqUpe1f1G5cmDTXj9wQYFp39lmKZ9YPUv4=');
        $headerSignatureInstance = new HeaderSignature($headerKey);

        $res = $headerSignatureInstance->getBinarySignatureValue();

        $this->assertSame(
            $baseEncoded,
            $res,
        );
    }

    /**
     * @covers HeaderSignature::getBinarySignatureValue
     * @return void
     * @throws SignatureExtractionException
     */
    public function testGetBinarySignatureValueThrow(): void
    {
        $this->expectExceptionObject(
            new SignatureExtractionException(
                'Can not base_decode64 the signature value "Gemgf5YIl0pbTUuU5gqaI+U4Br6SRCuODMwPFpHN08gPVqFQ67m0hBFmZu81s"',
            ),
        );

        $headerKey = 'testValue=:Gemgf5YIl0pbTUuU5gqaI+U4Br6SRCuODMwPFpHN08gPVqFQ67m0hBFmZu81s:';

        $headerSignatureInstance = new HeaderSignature($headerKey);

        $headerSignatureInstance->getBinarySignatureValue();
    }
}
