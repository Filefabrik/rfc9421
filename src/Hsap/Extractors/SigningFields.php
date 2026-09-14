<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Extractors;

use UnexpectedValueException;

/**
 * Extracting the Signing Fields
 */
final readonly class SigningFields
{
    /**
     * @param string $fieldString ("content-length" "@query-param" "example-dict")
     */
    public function __construct(public private(set) string $fieldString)
    {
    }

    /**
     * @return string[]
     * @example ['content-length', '@query-param', 'example-dict']
     */
    public function toArray(): array
    {
        $handleString = $this->validateRemoveBraces();

        $extracted = [];

        $parts = explode(' ', $handleString);
        foreach ($parts as $item) {
            if (str_starts_with($item, '"') && str_ends_with($item, '"')) {
                $first = substr($item, 1);

                $extracted[] = substr($first, 0, -1);
            } else {
                throw new UnexpectedValueException(sprintf('Unexpected value: %s', $item));
            }
        }

        return $extracted;
    }

    private function validateRemoveBraces(): string
    {
        if (str_starts_with($this->fieldString, '(') && str_ends_with($this->fieldString, ')')) {
            $first = substr($this->fieldString, 1);

            return substr($first, 0, -1);
        }

        throw new UnexpectedValueException('Unexpected start end of field.');
    }

}
