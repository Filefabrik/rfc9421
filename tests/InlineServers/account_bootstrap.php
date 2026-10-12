<?php

declare(strict_types=1);
function displayLogger(string $message, int $cnt = 1, bool $showLines = true): void
{
    $phpEol = str_repeat(PHP_EOL, $cnt);
    if ('' === $message) {
        echo $phpEol;

        return;
    }
    $parts = debug_backtrace()[0];

    $line = $showLines ?
        (str_replace('/var/www/html/tests/InlineServers', '', $parts['file'])).'::'.$parts['line'] .': ' :
        '';

    echo $line, $message, $phpEol;
}
