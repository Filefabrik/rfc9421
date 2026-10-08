<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Cryptography;

use Filefabrik\Rfc9421\Exceptions\SignatureException;
use Filefabrik\Rfc9421\Interfaces\SigningEnumInterface;
use Filefabrik\Rfc9421\Support\EnumSupportTrait;

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
        $found = self::isOwnType($key);
        if ($found instanceof SigningEnumInterface) {
            return $found;
        }
        /** @var string|null $key phpstan error */
        throw SignatureException::signatureEnumNotFound($key);
    }

}
