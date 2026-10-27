<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Extractors;

use Exception;
use Filefabrik\Rfc9421\Exceptions\SignatureExtractionException;
use Filefabrik\Rfc9421\Signature\ComponentName;
use Filefabrik\Rfc9421\Signature\ComponentNames;

/**
 * Extracting the Signing Fields from signature
 * Todo with structured fields extractor
 */
final readonly class Components
{
    /**
     * @param string $componentsString "content-length" "@query-param" "example-dict"
     */
    public function __construct(
        private(set) string $componentsString,
    ) {
    }

    /**
     * Many components separated by empty/space
     * Try to read the component name into safe usable ComponentItemDto
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     */
    public function extractToComponentsObject(): ComponentNames
    {
        $components = new ComponentNames();
        $parts      = explode(' ', $this->componentsString);
        foreach ($parts as $item) {
            try {
                $item = ComponentName::fromString($item);
            } catch (Exception) {
                throw new SignatureExtractionException(sprintf('Segment has unknown format "%s"', $item));
            }

            $components->set($item);
        }

        return $components;
    }


}
