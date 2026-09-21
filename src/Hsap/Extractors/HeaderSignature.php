<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Extractors;

use Filefabrik\Hsap\Exceptions\SignatureExtractionException;

/**
 * extracting the testValue=:Gemgf5YIl0pbTUuU5gqaI+U4Br6SRCuODMwPFpHN08gPVqFQ67m0hBFmZu81s:
 * extractSignatureNameKey()    testValue=
 * getSignatureValue()          Gemgf5YIl0pbTUuU5gqaI+U4Br6SRCuODMwPFpHN08gPVqFQ67m0hBFmZu81s
 * todo testing or checking null value extractSignatureNameKey
 */
final class HeaderSignature
{
    public private(set) array|null|SignatureExtractionException $extraction = null {
        /**
         * @throws SignatureExtractionException
         */
        get => $this->extraction ??= $this->getValidatedSignatureParts();
    }

    public function __construct(public private(set) readonly string $signatureString)
    {
    }

    /**
     * todo check, it is possible without signature
     *
     * @return string|null null on error
     */
    public function extractSignatureNameKey(): ?string
    {
        return $this->getValidExtraction()[1];
    }

    public function getValidExtraction(): null|array
    {
        return $this->extraction instanceof SignatureExtractionException ? null : $this->extraction;
    }

    /**
     *
     * @return string|null null on not found
     */
    public function getSignatureValue(): ?string
    {
        return $this->getValidExtraction()[2];
    }

    /**
     * checks we have the :base64EncodedValue-code: ...between the double colons
     *
     * @return array SignatureExtractionException on format missmatch
     *
     * @throws SignatureExtractionException
     */
    public function getValidatedSignatureParts(): array
    {
        $extracted = $this->extractSignatureString();
        $cnt       = count($extracted);
        //  extracted parts can be also have empty strings
        if (3 === $cnt) {
            return $extracted;
        }

        throw new SignatureExtractionException(
            sprintf('Can not extract signature parts. Needs 3 segments but has %s', $cnt),
        );
    }

    public function extractSignatureString(): array
    {
        // todo modify regex for strict format validation
        preg_match('#^(.*?):(.*?):$#', $this->signatureString, $matches);

        return $matches;
    }
}
