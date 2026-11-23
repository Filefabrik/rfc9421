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
 * 2.2.7. Query
 * Just like including an empty path component, the signer can include an empty query component to indicate that this component is not used in the message.
 * If the query string is absent from the request message, the component value is the leading ? character alone:
 * ?
 * resulting in the following signature base line:
 * "@query": ?
 *
 * @see https://www.rfc-editor.org/info/rfc3986/
 */
final class MessageQuery
{
    public static function handle(RequestInterface $requestInterface): string
    {
        $query = $requestInterface->getUri()
                                  ->getQuery()
        ;

        return (!str_starts_with($query, '?')) ? '?'.$query : $query;
    }
}
