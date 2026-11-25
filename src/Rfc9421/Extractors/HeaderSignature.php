<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Extractors;

use Filefabrik\Rfc9421\Exceptions\SignatureExtractionException;

/**
 * extracting the testValue=:Gemgf5YIl0pbTUuU5gqaI+U4Br6SRCuODMwPFpHN08gPVqFQ67m0hBFmZu81s:
 * extractSignatureNameKey()    testValue=
 * getSignatureValue()          Gemgf5YIl0pbTUuU5gqaI+U4Br6SRCuODMwPFpHN08gPVqFQ67m0hBFmZu81s
 * todo testing or checking null value extractSignatureNameKey
 */
final class HeaderSignature
{
    private static string $pattern = '#^(.*?):(.*?):$#';

    /**
     * @var array{non-falsy-string,string,string}|non-empty-array<string>|null
     */
    private(set) array|null $validatedSignatureParts = null;

    public function __construct(private(set) readonly string $signatureString)
    {
    }

    /**
     * todo check, it is possible without signature
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @return string null on error
     */
    public function extractSignatureNameKey(): string
    {
        // todo validate against null value
        return $this->getValidatedSignatureParts()[1] ?? '';
    }

    /**
     * checks we have the :base64EncodedValue-code: ...between the double colons
     *
     *
     * @throws SignatureExtractionException
     * @return array{0:non-falsy-string,1:string,2:string}|non-empty-array<string> SignatureExtractionException on format missmatch
     */
    private function getValidatedSignatureParts(): array
    {
        if (null !== $this->validatedSignatureParts) {
            return $this->validatedSignatureParts;
        }

        $extracted = $this->extractSignatureString();
        $cnt       = count($extracted);
        //  extracted initialContentsParameters can be also have empty strings
        if (3 === $cnt) {
            return $this->validatedSignatureParts = $extracted;
        }

        throw new SignatureExtractionException(
            sprintf('Can not extract signature parts. Needs 3 segments but has %s', $cnt),
        );
    }

    /**
     * @return array{non-falsy-string,string,string}|string[]
     */
    public function extractSignatureString(): array
    {
        $matches = [];
        // todo modify regex for strict format validation
        preg_match(self::$pattern, $this->signatureString, $matches);

        return $matches;
    }

    /**
     * todo check empty
     *
     * @throws SignatureExtractionException
     */
    public function getBinarySignatureValue(): string
    {
        $encoded = base64_decode($this->getSignatureValue(), true);
        if (false === $encoded) {
            throw new SignatureExtractionException(
                sprintf('Can not base_decode64 the signature value "%s"', $this->getSignatureValue()),
            );
        }

        return $encoded;
    }

    /**
     *  todo check empty ore null or exception
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function getSignatureValue(): string
    {
        return $this->getValidatedSignatureParts()[2] ?? '';
    }

}
