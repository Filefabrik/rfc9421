<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signer;

use Filefabrik\Rfc9421\Signature\Components;

/**
 * merges the signature params into sig1=("@method" "@authority" "@path" "content-type" "content-length")
 * todo make non-static methods
 */
final readonly class SigCompilerHelper
{
    public function __construct(
        private(set) Components $signatureComponents,
        private(set) string $signatureKeyName,
    ) {
    }

    /**
     *
     * @return string sig1=("@method";req "@authority" "@path" "content-type" "content-length");
     *
     */
    public function makeSig1fromSignatureParams(): string
    {
        return self::makeSig1fromComponents($this->signatureComponents, $this->signatureKeyName);
    }

    /**
     * @return string sig1=("@method" "@authority";req "@path" "content-type" "content-length")
     * @todo during concat with Metadata to signature-params don't forget the trailing semicolon
     */
    public static function makeSig1fromComponents(Components $components, string $signatureKeyName): string
    {
        return sprintf(
            '%s%s',
            $signatureKeyName,
            self::wrapBraces(self::compileFromArrayComponent($components->getComponents())),
        );
    }

    /**
     * simple helper
     */
    public static function wrapBraces(string $string): string
    {
        return sprintf('(%s)', $string);
    }

    public static function compileFromArrayComponent(array $componentsArray): string
    {
        $parts = [];
        foreach ($componentsArray as $component) {
            $name = sprintf('"%s"', $component->name);
            if ($component->initialContentsEnum) {
                $name .= ';'.$component->initialContentsEnum->name;
            }

            $parts[] = $name;
        }

        return implode(' ', $parts);
    }
}
