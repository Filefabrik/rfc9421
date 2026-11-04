<?php
/*
 * Copyright (c) 2026.  rt@filefabrik.com
 * proprietary
 * todo insert correct copyright
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Extractors;

use Exception;
use Filefabrik\Rfc9421\Exceptions\SignatureExtractionException;
use Filefabrik\Rfc9421\Signature\ComponentName;
use Filefabrik\Rfc9421\Signature\SigningComponents;
use Filefabrik\Rfc9421\Support\UnquoteHelper;

/**
 * Extracting the Signing Fields from signature
 * Todo with structured fields extractor
 */
final class Components
{
    /**
     * @param string $componentsString "content-length" "@query-param" "example-dict" or ("content-length" "@query-param" "example-dict")
     */
    public function __construct(
        private string $componentsString {
            set => UnquoteHelper::hasSurroundBraces($value) ? UnquoteHelper::removeSurroundingBraces($value) : $value;
        },
    ) {
    }

    /**
     * Many components separated by empty/space
     * Try to read the component name into safe usable ComponentItemDto
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     */
    public function extractToComponentsObject(): SigningComponents
    {
        $components = new SigningComponents();
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
