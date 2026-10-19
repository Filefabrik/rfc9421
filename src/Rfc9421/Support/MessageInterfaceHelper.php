<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Support;

use Filefabrik\Rfc9421\Exceptions\MethodException;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\UriInterface;
use Throwable;

/**
 * Ensure Methods exists and they are callable
 */
final class MessageInterfaceHelper
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     */
    public static function hasGetUri(\Psr\Http\Message\MessageInterface $message): UriInterface
    {
        self::isCallable($message, 'getUri');

        /* @var \Psr\Http\Message\RequestInterface $message */
        return $message->getUri();
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     */
    public static function isCallable(MessageInterface $message, string $method): void
    {
        if (!is_callable([$message, $method])) {
            throw MethodException::notCallable($message::class, $method);
        }
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     */
    public static function callExistingMethod(MessageInterface $message, string $method): mixed
    {
        self::isCallable($message, $method);

        try {
            return $message->{$method}();
        } catch (Throwable $throwable) {
            throw new MethodException($throwable->getMessage(), $throwable->getCode(), $throwable);
        }
    }
}
