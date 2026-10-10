<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Support;

use Filefabrik\Rfc9421\Exceptions\ContentDigestException;

/**
 * Body Signer digest hash handler
 */
enum DigestAlgorithmsEnum: string
{
    use EnumSupportTrait;

    /*
     * case is the php algo, the string value is used for prefixing sha-256=:tFdB/EN.....=: or sha-512=:tFdB/EN.....=:
     */
    case sha256 = 'sha-256';
    case sha512 = 'sha-512';

    /**
     * @throws ContentDigestException
     */
    public static function isOwnTypeOrThrow(self|string|null $key): self
    {
        if ($key instanceof self) {
            return $key;
        }

        if (($res = self::isOwnType($key)) instanceof self) {
            return $res;
        }

        throw ContentDigestException::hashAlgoNotSupported($key ?? '');
    }

    /**
     * used in php hash
     */
    public function getPhpAlgorithm(): string
    {
        return $this->name;
    }

    /**
     * used in signature
     */
    public function getDisplayAlgorithm(): string
    {
        return $this->value;
    }

}
