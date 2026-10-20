<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Methods;

use Psr\Http\Message\RequestInterface;

final class MessageTargetUri
{
    public static function handle(RequestInterface $requestInterface): string
    {
        $uri = $requestInterface->getUri();

        $parts = [
            $uri->getScheme(),
            '://',
            MessageAuthority::handle($requestInterface),
            $uri->getPath(),
        ];
        if ('' !== ($q = $uri->getQuery())) {
            $parts[] = '?';
            $parts[] = $q;
        }

        return implode('', $parts);
    }

}
