<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\StructuredFields\Processing;

use Bakame\Http\StructuredFields\Dictionary;
use Filefabrik\Rfc9421\Signature\InitialContentsEnum;
use Filefabrik\Rfc9421\StructuredFields\Entry;
use Filefabrik\Rfc9421\StructuredFields\MethodsMapper;
use Filefabrik\Rfc9421\StructuredFields\StructuredFields;
use Filefabrik\Rfc9421\Support\UnquoteHelper;

/**
 * Special Method to handle the InitialContentsEnum Key
 */
final readonly class Key
{
    public function __construct(private(set) MethodsMapper $methodsMapper, private(set) StructuredFields $structuredFieldTypes)
    {

    }

    /**
     * @throws \Bakame\Http\StructuredFields\StructuredFieldError
     */
    public function handle(Entry $entry, $childName): Entry
    {
        $value         = $this->applySingleKeyValue($entry->key, $childName, $entry->value);
        $modifiedValue = UnquoteHelper::quoteKEYValue($childName, $value);

        // important re-build the value
        return new Entry($entry->key, $modifiedValue, InitialContentsEnum::key);
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
