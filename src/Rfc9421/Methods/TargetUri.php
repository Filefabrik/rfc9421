<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Methods;

use Filefabrik\Rfc9421\Support\UriHelper;
use Psr\Http\Message\MessageInterface;

final class TargetUri
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     */
    public static function handle(MessageInterface $message): string
    {
        $uri = UriHelper::messageGetUriHelper($message);

        $parts = [
            $uri->getScheme(),
            '://',
            NormalizedAuthority::handle($message),
            $uri->getPath(),
        ];
        if ('' !== ($q = $uri->getQuery())) {
            $parts[] = '?';
            $parts[] = $q;
        }

        return implode('', $parts);
    }

}
