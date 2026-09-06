<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Signer;

use Filefabrik\Hsap\SignatureParams;

/**
 * merges the signature params into sig1=("@method" "@authority" "@path" "content-type" "content-length")
 */
final class SigCompilerHelper
{
    /**
     * @param SignatureParams $signatureParams
     *
     * @return string  "@method" "@authority" "@path" "content-type" "content-length"
     */
    public static function compileFromSignatureParams(SignatureParams $signatureParams): string
    {
        return self::compileFromArray($signatureParams->getSignatureParameters());
    }

    /**
     * @param array $params
     *
     * @return string "@method" "@authority" "@path" "content-type" "content-length"
     */
    public static function compileFromArray(array $params): string
    {
        return '"'.implode('" "', $params).'"';
    }

    /**
     * @param SignatureParams $signatureParams
     *
     * @return string sig1=("@method" "@authority" "@path" "content-type" "content-length");
     *
     */
    public static function makeSig1fromSignatureParams(SignatureParams $signatureParams): string
    {
        return self::makeSig1fromArray($signatureParams->getSignatureParameters());
    }

    /**
     * @param array $params
     *
     * @return string sig1=("@method" "@authority" "@path" "content-type" "content-length");
     */
    public static function makeSig1fromArray(array $params): string
    {
        return 'sig1=('.self::compileFromArray($params).');';
    }
}
