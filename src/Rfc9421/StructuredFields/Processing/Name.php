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

use Bakame\Http\StructuredFields\Bytes;
use Bakame\Http\StructuredFields\DisplayString;
use Bakame\Http\StructuredFields\Token;
use DateTimeImmutable;
use Filefabrik\Rfc9421\Signature\ComponentBuilder;
use Filefabrik\Rfc9421\StructuredFields\EntryWithValue;

/**
 * Special Method to handle the InitialContentsEnum ;fieldName
 * This class only loops through because it will be only used by the @query-params which handles the value extraction
 */
final readonly class Name
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public function handle(EntryWithValue $entry, Bytes|DisplayString|Token|bool|DateTimeImmutable|float|int|string|null $keyName): EntryWithValue
    {
        // important re-build the value
        return EntryWithValue::fromBuilder(
            builder: ComponentBuilder::from($entry->getFieldName())
                                     ->withName($keyName),
            value  : $entry->getValue(),
        );
    }

}
