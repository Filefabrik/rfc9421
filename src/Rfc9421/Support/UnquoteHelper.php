<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * todo insert correct copyright
 */
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
     */
    public static function unquote(string $string): string
    {
        if (self::isQuoted($string)) {
            $first = substr($string, 1);

            return substr($first, 0, -1);
        }

        throw new UnexpectedValueException(
            sprintf('The expected value has to be wrapped by quotes "value" but is %s.', $string),
        );
    }

    public static function isQuoted(string $string): bool
    {
        return (self::startsWithQuote($string) && str_ends_with($string, '"'));
    }

    public static function startsWithQuote(string $string): bool
    {
        return str_starts_with($string, '"');
    }

    public static function quotedUnderscore(string $string): string
    {
        return self::quote(self::underscore($string));
    }

    public static function quote(string $string): string
    {
        return '"'.$string.'"';
    }

    public static function underscore(string $string): string
    {
        return '_'.$string.'_';
    }

    public static function quoteKEYValue(string $keyName, string $value): string
    {
        return self::quote($keyName).': '.$value;
    }

    public static function removeSurroundingBraces(string $string): string
    {
        return self::removeEndBrace(self::removeStartBrace($string));
    }

    public static function removeEndBrace(string $string): string
    {
        if (self::endsWithBrace($string)) {
            return substr($string, 0, -1);
        }

        throw new UnexpectedValueException(
            sprintf('There was no brace ")" at the end value:"%s".', $string),
        );
    }

    public static function endsWithBrace(string $string): bool
    {
        return str_ends_with($string, ')');
    }

    public static function removeStartBrace(string $string): string
    {
        if (self::startsWithBrace($string)) {
            return substr($string, 1);
        }

        throw new UnexpectedValueException(
            sprintf('There was no brace "(" at the beginning value:"%s".', $string),
        );
    }

    public static function startsWithBrace(string $string): bool
    {
        return str_starts_with($string, '(');
    }

    public static function hasSurroundBraces(string $string): bool
    {
        return self::startsWithBrace($string) && self::endsWithBrace($string);
    }
}
