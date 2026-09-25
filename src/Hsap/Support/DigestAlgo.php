<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Support;

use Filefabrik\Hsap\Exceptions\ContentDigestException;

final class DigestAlgo
{
    /**
     * @throws ContentDigestException
     */
    public static function validateContentDigestHashAlgo(?string $contentDigestHashAlgo): ?string
    {
        return null === $contentDigestHashAlgo ? null : match ($contentDigestHashAlgo) {
            'sha256', 'sha-256' => 'sha256',
            'sha512', 'sha-512' => 'sha512',
            default             => throw ContentDigestException::hashAlgoNotSupported($contentDigestHashAlgo),
        };
    }

}
