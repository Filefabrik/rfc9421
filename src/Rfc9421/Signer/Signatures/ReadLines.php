<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signer\Signatures;

/**
 * after verify, so nothing can be written into the ReadLines
 */
final class ReadLines extends AbstractLines
{
    /**
     * @param array<string|int,string|\Filefabrik\Rfc9421\Signer\Signatures\Line>|array{}|\Filefabrik\Rfc9421\Signer\Signatures\WriteLines $lines
     */
    public function __construct(array|WriteLines $lines)
    {
        $this->setup($lines);
    }

    /**
     * @param array<string|int,string|\Filefabrik\Rfc9421\Signer\Signatures\Line>|array{}|\Filefabrik\Rfc9421\Signer\Signatures\WriteLines $lines
     */
    private function setup(array|WriteLines $lines): void
    {
        if ($lines instanceof WriteLines) {
            $lines = $lines->toKeyValueArray();
        }

        foreach ($lines as $key => $line) {
            if ($line instanceof Line && is_int($key)) {
                $this->protectedAdd($line);
            } else {
                /* @var string $key */
                /* @var string $line */
                $this->protectedAddEntry($key, $line);
            }
        }
    }
}

