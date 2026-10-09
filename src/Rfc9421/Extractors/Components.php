<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Extractors;

use Filefabrik\Rfc9421\Support\UnquoteHelper;

/**
 * Extracting the Signing Fields
 */
final readonly class Components
{
    /**
     * @param string $componentsString "content-length" "@query-param" "example-dict"
     */
    public function __construct(private(set) string $componentsString)
    {
    }

    /**
     * @return string[]|array<void>
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
