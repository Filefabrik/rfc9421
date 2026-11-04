<?php
/*
 * Copyright (c) 2026.  rt@filefabrik.com
 * proprietary
 * todo insert correct copyright
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Methods;

use Filefabrik\Rfc9421\Support\MessageInterfaceHelper;
use Psr\Http\Message\MessageInterface;

final class MessageScheme
{
    /**
     * todo check allowed schemes
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     */
    public static function handle(MessageInterface $message): string
    {
        return strtolower(
            MessageInterfaceHelper::hasGetUri($message)
                                  ->getScheme(),
        );
    }
}
