<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Support;

use Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\UriInterface;

final class UriHelper
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     */
    public static function messageGetUriHelper(MessageInterface $interface): UriInterface
    {
        if (!is_callable([$interface, 'getUri'])) {
            throw  new UnprocessableSignatureException('Unable to extract authority from MessageInterface');
        }

        return $interface->getUri();
    }
}
