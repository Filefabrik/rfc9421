<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Extractors;

use Filefabrik\Rfc9421\Exceptions\SignatureExtractionException;
use Filefabrik\Rfc9421\Extractors\HeaderSignature;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(HeaderSignature::class)]
final class HeaderSignatureTest extends TestCase
{
    public function testExtractSignatureName(): void
    {
        $headerKey               = 'testValue=:Gemgf5YIl0pbTUuU5gqaI+U4Br6SRCuODMwPFpHN08gPVqFQ67m0hBFmZu81s:';
        $headerSignatureInstance = new HeaderSignature($headerKey);
        self::assertSame('testValue=', $headerSignatureInstance->extractSignatureNameKey());
    }

    public function testExtractSignatureNameEmpty(): void
    {
        $headerKey               = ':Gemgf5YIl0pbTUuU5gqaI+U4Br6SRCuODMwPFpHN08gPVqFQ67m0hBFmZu81s:';
        $headerSignatureInstance = new HeaderSignature($headerKey);
        self::assertSame('', $headerSignatureInstance->extractSignatureNameKey());
    }

    public function testExtractSignatureNameNotFound(): void
    {
        $this->expectExceptionObject(
            new SignatureExtractionException('Can not extract signature parts. Needs 3 segments but has 0'),
        );
        $headerKey = 'Gemgf5YIl0pbTUuU5gqaI+U4Br6SRCuODMwPFpHN08gPVqFQ67m0hBFmZu81s';
        new HeaderSignature($headerKey)->extractSignatureNameKey();
    }

    public function testGetSignatureValue(): void
    {
        $headerKey               = 'testValue=:Gemgf5YIl0pbTUuU5gqaI+U4Br6SRCuODMwPFpHN08gPVqFQ67m0hBFmZu81s:';
        $headerSignatureInstance = new HeaderSignature($headerKey);
        self::assertSame(
            'Gemgf5YIl0pbTUuU5gqaI+U4Br6SRCuODMwPFpHN08gPVqFQ67m0hBFmZu81s',
            $headerSignatureInstance->getSignatureValue(),
        );
    }

    public function testGetSignatureValueEmpty(): void
    {
        $headerKey               = 'testValue=::';
        $headerSignatureInstance = new HeaderSignature($headerKey);
        self::assertSame('', $headerSignatureInstance->getSignatureValue());
    }

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

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testGetBinarySignatureValue(): void
    {
        //todo not clear, test deeper
        // hmac 256
        $headerKey = 'testValue=:Q94UwavaMSqUpe1f1G5cmDTXj9wQYFp39lmKZ9YPUv4=:';

        $baseEncoded             = base64_decode('Q94UwavaMSqUpe1f1G5cmDTXj9wQYFp39lmKZ9YPUv4=');
        $headerSignatureInstance = new HeaderSignature($headerKey);

        $res = $headerSignatureInstance->getBinarySignatureValue();

        self::assertSame(
            $baseEncoded,
            $res,
        );
    }

    /**
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
