<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Signer;

use Filefabrik\Hsap\SignatureParams;

/**
 * merges the signature params into sig1=("@method" "@authority" "@path" "content-type" "content-length")
 * todo make non-static methods
 */
final readonly class SigCompilerHelper
{
    public function __construct(
        public private(set) SignatureParams $signatureParams,
        public private(set) string $signatureKeyName,
    ) {
    }

    /**
     *
     * @return string sig1=("@method" "@authority" "@path" "content-type" "content-length");
     *
     */
    public function makeSig1fromSignatureParams(): string
    {
        return self::makeSig1fromArray($this->signatureParams->getSignatureParameters(), $this->signatureKeyName);
    }

    /**
     * @param array  $params
     * @param string $signatureKeyName
     *
     * @return string sig1=("@method" "@authority" "@path" "content-type" "content-length")
     * @todo during concat with Metadata to signature-params don't forget the trailing semicolon
     */
    public static function makeSig1fromArray(array $params, string $signatureKeyName): string
    {
        // todo ..with signature key
        return sprintf('%s%s', $signatureKeyName, self::wrapBraces(self::compileFromArray($params)));
    }

    /**
     * simple helper
     *
     * @param string $string
     *
     * @return string
     */
    public static function wrapBraces(string $string): string
    {
        return sprintf('(%s)', $string);
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
}
