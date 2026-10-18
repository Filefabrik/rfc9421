<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\StructuredFields\Processing;

use Filefabrik\Rfc9421\Signature\InitialContentsEnum;
use Filefabrik\Rfc9421\StructuredFields\Entry;
use Psr\Http\Message\MessageInterface;

final class Bs
{
    public function handle(Entry $entry, MessageInterface $whichRequest): Entry|null
    {
        $values = $whichRequest->getHeader($entry->key);
        // nothing to handle so return empty
        if (0 === count($values)) {
            return null;
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
