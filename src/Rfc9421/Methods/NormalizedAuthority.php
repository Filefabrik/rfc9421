<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Methods;

use Psr\Http\Message\RequestInterface;

final class NormalizedAuthority
{
    /**
     * $interface->getUri()->getAuthority() requires additional filtering for RFC-9421.
     * It must be lowercase and must not contain a port value.
     * todo check comment
     *
     */
    public static function handle(RequestInterface $requestInterface): string
    {
        $uri = $requestInterface->getUri();

        $authority = strtolower($uri->getAuthority());
        $authority = explode(':', $authority);

        return $authority[0];
    }
}
