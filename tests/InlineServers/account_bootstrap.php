<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * todo insert correct copyright
 */
declare(strict_types=1);
/**
 * Console logging
 */
function displayLogger(string $message, int $cnt = 1, bool $showLines = true): void
{
    $phpEol = str_repeat(PHP_EOL, $cnt);
    if ('' === $message) {
        echo $phpEol;

        return;
    }
    $parts = debug_backtrace()[0];

    $p = explode('/tests/InlineServers', $parts['file'] ?? '');

    $line = $showLines ? implode('', [$p[1] ?? '', '::', $parts['line'] ?? ' ', ': ']) : '';

    echo $line, $message, $phpEol;
}
