<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Methods;

use Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException;
use Filefabrik\Rfc9421\Support\UriHelper;
use Psr\Http\Message\MessageInterface;

final class NormalizedAuthority
{
    /**
     * $interface->getUri()->getAuthority() requires additional filtering for RFC-9421.
     * It must be lowercase and must not contain a port value.
     * todo check comment
     *
     * @throws UnProcessableSignatureException
     */
    public static function handle(MessageInterface $message): string
    {
        $uri = UriHelper::messageGetUriHelper($message);

        $authority = strtolower($uri->getAuthority());
        $authority = explode(':', $authority);

        return $authority[0];
    }
}
