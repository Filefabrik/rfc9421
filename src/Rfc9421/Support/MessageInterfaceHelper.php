<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * todo insert correct copyright
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Support;

use Filefabrik\Rfc9421\Exceptions\MethodException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\UriInterface;

/**
 * Ensure Methods exists and they are callable
 */
final class MessageInterfaceHelper
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     */
    public static function hasGetUri(MessageInterface $message): UriInterface
    {
        $method = 'getUri';
        if (!is_callable([$message, $method])) {
            throw MethodException::notCallable($message::class, $method);
        }

        /* @var \Psr\Http\Message\RequestInterface $message */
        return $message->getUri();
    }
}
