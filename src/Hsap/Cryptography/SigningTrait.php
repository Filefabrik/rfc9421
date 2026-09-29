<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Cryptography;

use Filefabrik\Hsap\Exceptions\SignatureException;
use Filefabrik\Hsap\Interfaces\SigningEnumInterface;
use Filefabrik\Hsap\Support\EnumSupportTrait;

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
        return self::isOwnType($key) ?? throw SignatureException::signatureEnumNotFound($key);
    }

}
