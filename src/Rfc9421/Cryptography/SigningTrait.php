<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Cryptography;

use Filefabrik\Rfc9421\Enums\Support\EnumSupportTrait;
use Filefabrik\Rfc9421\Exceptions\SignatureException;
use Filefabrik\Rfc9421\Interfaces\SigningEnumInterface;

trait SigningTrait
{
    use EnumSupportTrait;

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public static function nameOrThrow(SigningEnumInterface|string|null $key): string
    {
        return self::isOwnTypeOrThrow($key)
                   ->getName()
        ;
    }

    /**
     * @throws SignatureException
     */
    public static function isOwnTypeOrThrow(SigningEnumInterface|string|null $key): SigningEnumInterface
    {
        $found = self::isOwnType($key);
        if ($found instanceof SigningEnumInterface) {
            return $found;
        }

        throw SignatureException::signatureEnumNotFound($key);
    }

}
