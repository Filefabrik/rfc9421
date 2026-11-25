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

use Filefabrik\Rfc9421\Exceptions\UnexpectedValue;

/**
 * after verify, so nothing can be written into the ReadLines
 */
final class ReadLines extends AbstractLines
{
    /**
     * @param array<string|int,string|\Filefabrik\Rfc9421\Signer\Signatures\Line>|array{}|\Filefabrik\Rfc9421\Signer\Signatures\WriteLines $lines
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function __construct(array|WriteLines $lines)
    {
        $this->setup($lines);
    }

    /**
     * @param array<string|int,string|\Filefabrik\Rfc9421\Signer\Signatures\Line>|array{}|\Filefabrik\Rfc9421\Signer\Signatures\WriteLines $lines
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    private function setup(array|WriteLines $lines): void
    {
        if ($lines instanceof WriteLines) {
            $lines = $lines->toKeyValueArray();
        }

        foreach ($lines as $key => $line) {
            if ($line instanceof Line && is_int($key)) {
                $this->protectedAdd($line);
            } elseif ((is_string($line) || is_int($line)) && is_string($key)) {
                $this->protectedAddEntry($key, $line);
            } else {
                throw new UnexpectedValue(
                    sprintf('Unexpected value of type %s or key of type %s', gettype($line), gettype($key)),
                );
            }
        }
    }
}

