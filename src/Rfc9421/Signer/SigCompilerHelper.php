<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signer;

use Filefabrik\Rfc9421\SigningComponents;

/**
 * merges the signature params into sig1=("@method" "@authority" "@path" "content-type" "content-length")
 * todo make non-static methods
 *
 */
final readonly class SigCompilerHelper
{
    public function __construct(
        private(set) SigningComponents $signatureComponents,
        private(set) string $signatureKeyName,
    ) {
    }

    /**
     *
     *
     * @return string sig1=("@method";asReq "@authority" "@path" "content-type" "content-length");
     */
    public function makeSig1fromSignatureParams(): string
    {
        return self::makeSig1fromComponents($this->signatureComponents, $this->signatureKeyName);
    }

    /**
     *
     * @return string sig1=("@method" "@authority";asReq "@path" "content-type" "content-length")
     * @todo during concat with Metadata to signature-params don't forget the trailing semicolon
     */
    public static function makeSig1fromComponents(SigningComponents $components, string $signatureKeyName): string
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
    private static function wrapBraces(string $string): string
    {
        return sprintf('(%s)', $string);
    }

    /**
     * todo that is wrong, must use the component-parser
     *
     */
    private static function compileFromArrayComponent(SigningComponents $components): string
    {
        $parts = [];

        foreach ($components->getComponents() as $component) {
            $parts[] = $component->getItem()
                                 ->toHttpValue()
            ;
        }

        return implode(' ', $parts);
    }
}
