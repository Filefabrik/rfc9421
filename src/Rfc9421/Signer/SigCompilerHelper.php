<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signer;

use Filefabrik\Rfc9421\Signature\ComponentNames;

/**
 * merges the signature params into sig1=("@method" "@authority" "@path" "content-type" "content-length")
 * todo make non-static methods
 *
 */
final readonly class SigCompilerHelper
{
    public function __construct(
        private(set) ComponentNames $signatureComponents,
        private(set) string $signatureKeyName,
    ) {
    }

    /**
     *
     * @return string sig1=("@method";req "@authority" "@path" "content-type" "content-length");
     *
     * @throws \Exception
     */
    public function makeSig1fromSignatureParams(): string
    {
        return self::makeSig1fromComponents($this->signatureComponents, $this->signatureKeyName);
    }

    /**
     *
     * @return string sig1=("@method" "@authority";req "@path" "content-type" "content-length")
     * @throws \Exception
     * @todo during concat with Metadata to signature-params don't forget the trailing semicolon
     */
    public static function makeSig1fromComponents(ComponentNames $components, string $signatureKeyName): string
    {
        return sprintf(
            '%s%s',
            $signatureKeyName,
            self::wrapBraces(self::compileFromArrayComponent($components)),
        );
    }

    /**
     * simple helper
     */
    public static function wrapBraces(string $string): string
    {
        return sprintf('(%s)', $string);
    }

    /**
     * todo that is wrong, must use the component-parser
     *
     * @throws \Exception
     */
    public static function compileFromArrayComponent(ComponentNames $components): string
    {
        $parts = [];

        foreach ($components->getComponents() as $component) {
            $parts[] = $component->getItem()->toHttpValue();
        }

        return implode(' ', $parts);
    }
}
