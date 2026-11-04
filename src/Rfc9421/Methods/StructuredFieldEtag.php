<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * todo insert correct copyright
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Methods;

/**
 * handle etag method
 */
final class StructuredFieldEtag
{
    public static function handle(string $fieldValue): string
    {
        $resultParts = [];
        $list        = explode(',', $fieldValue);
        foreach ($list as $item) {
            $trimmed = trim($item);

            $resultParts[] = str_starts_with($trimmed, 'W/') ? substr($trimmed, 2).'; w' : $trimmed;
        }

        return implode(', ', $resultParts);
    }
}
