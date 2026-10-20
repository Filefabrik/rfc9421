<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Methods;

use Psr\Http\Message\RequestInterface;

final class MesssageRequestTarget
{
    public static function handle(RequestInterface $requestInterface): string
    {
        return $requestInterface->getRequestTarget();
    }
}
