<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Extractors;

use Filefabrik\Rfc9421\Exceptions\SignatureExtractionException;
use Filefabrik\Rfc9421\Signature\Component;
use Filefabrik\Rfc9421\Signature\InitialContentsEnum;

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
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     */
    public function extractToComponentsObject(): \Filefabrik\Rfc9421\Signature\Components
    {
        $components = new \Filefabrik\Rfc9421\Signature\Components();
        $parts      = explode(' ', $this->componentsString);
        foreach ($parts as $item) {
            $components->set($this->extractSegment($item));
        }

        return $components;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @example '"content-something";req' '"content-something"'
     */
    public function extractSegment(string $segment): Component
    {
        // todo, if there an ";" but no following expression "InitialContentsEnum" throw

        $simple = '#^"(.*?)"$#';
        preg_match($simple, $segment, $matches);

        if (2 === count($matches)) {
            return new Component($matches[1], null);
        }

        $s = implode('|', InitialContentsEnum::names());
        preg_match('#^"(.*?)";('.$s.')$#', $segment, $matches);

        if (3 === count($matches)) {
            return new Component($matches[1], $matches[2]);
        }

        throw new SignatureExtractionException(sprintf('Segment has unknown format "%s"', $segment));
    }
}
