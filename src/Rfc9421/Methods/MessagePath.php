<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Methods;

use Psr\Http\Message\RequestInterface;

/**
 * todo check trailing slash
 * https://example.com/testing/something/
 * https://example.com/testing/something
 *
 * @see https://url.spec.whatwg.org/#example-5434421b
 */
final class MessagePath
{
    public static function handle(RequestInterface $requestInterface): string
    {
        $p = $requestInterface->getUri()
                              ->getPath()
        ;

        return !str_starts_with($p, '/') ? '/' : $p;
    }
}
