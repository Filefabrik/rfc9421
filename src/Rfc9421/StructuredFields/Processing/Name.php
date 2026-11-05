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

/**
 * Special Method to handle the InitialContentsEnum ;name
 * This class only loops through because it will be only used by the @query-params which handles the value extraction
 */
final readonly class Name
{
    public function handle(Entry $entry, string $keyName): Entry
    {
        // important re-build the value
        return new Entry($entry->key, $entry->value, InitialContentsEnum::name, $keyName);
    }

}
