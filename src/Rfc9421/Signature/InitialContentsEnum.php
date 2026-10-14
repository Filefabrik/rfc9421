<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signature;

use Filefabrik\Rfc9421\Exceptions\EnumTypeException;

/**
 * 6.5.2. Initial Contents
 * The table below contains the initial contents of the "HTTP Signature Component Parameters" registry.
 *
 * @see https://www.rfc-editor.org/info/rfc9421/#section-6.5.2
 */
enum InitialContentsEnum
{
    /**
     * sf    Strict Structured Field serialization    Section 2.1.1 of RFC 9421
     */
    case sf;
    /**
     * key    Single key value of Dictionary Structured Fields    Section 2.1.2 of RFC 9421
     */
    case key;
    /**
     * bs    Byte Sequence wrapping indicator
     */
    case bs;
    /**
     * tr    Trailer    Section 2.1.4 of RFC 9421
     */
    case tr;
    /**
     * Related request indicator
     * req    Related request indicator    Section 2.4 of RFC 9421
     */
    case req;
    /**
     * name    Single named query parameter    Section 2.2.8 of RFC 9421
     */
    case name;

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public static function isOwnTypeOrNullOrThrow(string|self|null $key): self|null
    {
        if (null === $key || $key instanceof self) {
            return $key;
        }

        if (($found = self::isOwnTypeOrFalse($key)) instanceof self) {
            return $found;
        }
        throw EnumTypeException::TypeError($key, self::class);
    }

    public static function isOwnTypeOrFalse(string|self $key): self|null
    {
        return array_find(self::cases(), fn ($case): bool => $case->name === $key);
    }
}
