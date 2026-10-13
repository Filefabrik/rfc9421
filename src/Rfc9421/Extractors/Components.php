<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Extractors;

use Filefabrik\Rfc9421\Exceptions\SignatureExtractionException;
use Filefabrik\Rfc9421\Signature\Component;
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
     * @return array{int?:string}
     * @todo    extract to signature components
     * @example ['content-length', '@query-param', 'example-dict']
     * @deprecated
     */
    public function toArray(): array
    {
        $extracted = [];

        $parts = explode(' ', $this->componentsString);
        foreach ($parts as $item) {
            // todo extract the expression '"content-length" "@query-param";req'
            $extracted[] = UnquoteHelper::unquote($item);
        }

        return $extracted;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function extractToComponentsObject(): \Filefabrik\Rfc9421\Signature\Components
    {
        $components = new \Filefabrik\Rfc9421\Signature\Components();
        $parts      = explode(' ', $this->componentsString);
        foreach ($parts as $item) {
            $components->add($this->extractSegment($item));
            // todo extract the expression '"content-length" "@query-param";req'

        }

        return $components;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function extractSegment(string $segment): Component
    {
        $parts = explode(';', $segment);
        if (2 === count($parts)) {
            [$key, $contentEnum] = $parts;
        } elseif (1 === count($parts)) {
            $key         = $parts[0];
            $contentEnum = null;
        } else {
            throw new SignatureExtractionException('Segment error not 2 parts');
        }

        $key = UnquoteHelper::unquote($key);

        return new Component($key, $contentEnum);
    }
}
