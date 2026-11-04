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

/**
 * @codeCoverageIgnore
 */
trait SigningTrait
{
    use EnumSupportTrait;

    /**
     * @throws SignatureException
     */
    public static function isOwnTypeOrThrow(SigningEnumInterface|string|null $key): SigningEnumInterface
    {
        if ($key instanceof SigningEnumInterface) {
            return $key;
        }

        $found = self::isOwnType($key);
        if ($found instanceof SigningEnumInterface) {
            return $found;
        }

        throw SignatureException::signatureEnumNotFound($key);
    }

}
