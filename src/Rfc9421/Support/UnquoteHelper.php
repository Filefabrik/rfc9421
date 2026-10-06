<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Support;

use UnexpectedValueException;

/**
 * Helper to remove the required Quotes for a string
 */
final class UnquoteHelper
{
    /**
     * Removes the quotes from a string
     *
     * @param string $string
     *
     * @return string
     */
    public static function unquote(string $string): string
    {
        if (str_starts_with($string, '"') && str_ends_with($string, '"')) {
            $first = substr($string, 1);

            return substr($first, 0, -1);
        }
        throw new UnexpectedValueException(
            sprintf('The expected value has to be wrapped by quotes "value" but is %s.', $string),
        );
    }
}
