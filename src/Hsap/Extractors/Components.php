<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Extractors;

use Filefabrik\Hsap\Support\UnquoteHelper;

/**
 * Extracting the Signing Fields
 */
final readonly class Components
{
    /**
     * @param string $componentsString "content-length" "@query-param" "example-dict"
     */
    public function __construct(public private(set) string $componentsString)
    {
    }

    /**
     * @return string[]
     * @example ['content-length', '@query-param', 'example-dict']
     */
    public function toArray(): array
    {
        $extracted = [];

        $parts = explode(' ', $this->componentsString);
        foreach ($parts as $item) {
            $extracted[] = UnquoteHelper::unquote($item);
        }

        return $extracted;
    }

}
