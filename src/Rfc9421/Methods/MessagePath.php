<?php
/*
 * Copyright (c) 2026.  rt@filefabrik.com
 * proprietary
 * todo insert correct copyright
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Methods;

use Psr\Http\Message\RequestInterface;

/**
 * todo check trailing slash
 * https://example.com/testing/something/
 * https://example.com/testing/something
 */
final class MessagePath
{
    public static function handle(RequestInterface $requestInterface): string
    {
        $p = $requestInterface->getUri()
                              ->getPath()
        ;

        return (!str_starts_with($p, '/')) ? '/'.$p : $p;
    }
}
