<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\StructuredFields\Processing;

use Bakame\Http\StructuredFields\Dictionary;
use Filefabrik\Rfc9421\Signature\InitialContentsEnum;
use Filefabrik\Rfc9421\StructuredFields\Entry;
use Filefabrik\Rfc9421\StructuredFields\StructuredFields;
use Filefabrik\Rfc9421\Support\UnquoteHelper;

/**
 * Special Method to handle the InitialContentsEnum Key
 */
final readonly class Key
{
    public function __construct(private(set) StructuredFields $structuredFieldTypes)
    {
    }

    /**
     * @throws \Bakame\Http\StructuredFields\StructuredFieldError
     */
    public function handle(Entry $entry, string $keyName): Entry
    {
        // todo check fieldValue cast because only strings allowed
        $entryValue    = (string)$entry->value;
        $value         = $this->applySingleKeyValue(name: $entry->key, key: $keyName, fieldValue: $entryValue);
        $modifiedValue = UnquoteHelper::quoteKEYValue(keyName: $keyName, value: $value);

        // important re-build the value
        return new Entry(key: $entry->key, value: $modifiedValue, initialContents: InitialContentsEnum::key);
    }

    /**
     * @throws \Bakame\Http\StructuredFields\StructuredFieldError
     */
    private function applySingleKeyValue(string $name, string $key, string $fieldValue): string
    {
        $type = $this->structuredFieldTypes->getOrNull($name);
        if ((null !== $type && '' !== $type)) {
            $dictionary = Dictionary::fromHttpValue($fieldValue);
            if ($dictionary->isNotEmpty() && isset($dictionary[$key])) {
                return $dictionary[$key]->toHttpValue();
            }
        }

        return '';
    }
}
