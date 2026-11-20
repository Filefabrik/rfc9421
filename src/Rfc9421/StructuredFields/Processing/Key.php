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

use Filefabrik\Rfc9421\Bakame\Dictionary;
use Filefabrik\Rfc9421\Signature\ComponentBuilder;
use Filefabrik\Rfc9421\StructuredFields\EntryWithValue;
use Filefabrik\Rfc9421\StructuredFields\StructuredFields;

/**
 * Special Method to handle the InitialContentsEnum Key
 */
final readonly class Key
{
    public function __construct(private(set) StructuredFields $structuredFieldTypes)
    {
    }

    /**
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function handle(EntryWithValue $entry, string $keyName): EntryWithValue
    {
        // todo check fieldValue cast because only strings allowed
        $entryValue = (string)$entry->getValue();
        $value      = $this->applySingleKeyValue(name: $entry->getFieldName(), key: $keyName, fieldValue: $entryValue);

        return EntryWithValue::fromBuilder(new ComponentBuilder($entry->getFieldName())->withKey($keyName))
                             ->setValue($value)
        ;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     */
    private function applySingleKeyValue(string $name, string $key, string $fieldValue): string
    {
        $type = $this->structuredFieldTypes->getOrNull($name);
        if ((null !== $type && '' !== $type)) {
            $dictionary = Dictionary::fromHttpValue(headerValue: $fieldValue);
            if ($dictionary->isNotEmpty() && isset($dictionary[$key])) {
                return $dictionary[$key]->toHttpValue();
            }
        }

        return '';
    }
}
