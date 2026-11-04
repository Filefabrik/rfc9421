<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\StructuredFields\Processing;

use Filefabrik\Rfc9421\Enums\InitialContentsEnum;
use Filefabrik\Rfc9421\StructuredFields\Entry;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @see 2.1.3. Binary-Wrapped HTTP Fields
 * @see https://www.rfc-editor.org/info/rfc9421/#section-2.1.3-4
 * Example-Header: value, with, lots
 * Example-Header: of, commas
 * "example-header";bs: :dmFsdWUsIHdpdGgsIGxvdHM=:, :b2YsIGNvbW1hcw==:
 */
final class Bs
{
    public function handle(Entry $entry, ResponseInterface|RequestInterface $messageInterface): Entry
    {
        $values = $messageInterface->getHeader($entry->key);
        // nothing to handle so return empty
        // todo check if it is an error that no http-header value found and whats happened with the zero value
        if (0 === count($values)) {
            return $entry->setInitialContents(InitialContentsEnum::bs);
        }

        $valuesSegments = [];
        foreach ($values as $value) {
            $valuesSegments [] = ':'.base64_encode(trim($value)).':';
        }

        // all in a line
        $resultValue = implode(', ', $valuesSegments);

        return $entry->setInitialContents(InitialContentsEnum::bs)
                     ->setValue($resultValue)
        ;
    }

}
