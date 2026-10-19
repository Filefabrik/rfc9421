<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Methods;

use Psr\Http\Message\RequestInterface;

final class Path
{
    public static function handle(RequestInterface $requestInterface): string
    {
        $p = $requestInterface->getUri()
                              ->getPath()
        ;

        return (!str_starts_with($p, '/')) ? '/'.$p : $p;
    }
}
