<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * todo insert correct copyright
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Examples\Tools;

use JsonException;

final class JsonToString
{
    public static function toString(array $data): ?string
    {
        try {
            $bodyJson = json_encode($data, JSON_THROW_ON_ERROR);
        } catch (JsonException $jsonException) {
            echo $jsonException->getMessage();

            return null;
        }

        return $bodyJson;
    }
}
