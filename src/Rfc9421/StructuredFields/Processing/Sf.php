<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\StructuredFields\Processing;

use Bakame\Http\StructuredFields\Dictionary;
use Bakame\Http\StructuredFields\InnerList;
use Bakame\Http\StructuredFields\Item;
use Bakame\Http\StructuredFields\OuterList;
use Bakame\Http\StructuredFields\Parameters;
use Exception;
use Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException;
use Filefabrik\Rfc9421\Signature\InitialContentsEnum;
use Filefabrik\Rfc9421\StructuredFields\Entry;
use Filefabrik\Rfc9421\StructuredFields\MethodsMapper;
use Filefabrik\Rfc9421\StructuredFields\StructuredFields;

final readonly class Sf
{
    public function __construct(private(set) MethodsMapper $methodsMapper, private(set) StructuredFields $structuredFieldTypes)
    {

    }

    /**
     * @throws \Bakame\Http\StructuredFields\StructuredFieldError
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     */
    public function handle(Entry $entry): Entry
    {
        $value = $this->applyStructuredField($entry);

        return new Entry($entry->key, $value, InitialContentsEnum::sf);
    }

    /**
     * @throws \Bakame\Http\StructuredFields\StructuredFieldError
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     * @throws Exception
     */
    private function applyStructuredField(Entry $entry): string
    {
        $type  = $this->structuredFieldTypes->getOrThrow($entry->key);
        $field = $this->methodsMapper->callMethod($type, $entry->value);
        if (!$field) {
            throw new UnProcessableSignatureException('Unknown or unregistered structured field type');
        }

        return array_any(
            [OuterList::class, InnerList::class, Parameters::class, Dictionary::class, Item::class],
            fn (string $className): bool => $field instanceof $className,
        )
            ? $field->toHttpValue() : $field;
    }
}
