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

use Bakame\Http\StructuredFields\{Dictionary, InnerList, Item, OuterList, Parameters};
use Filefabrik\Rfc9421\Exceptions\StructuredFieldException;
use Filefabrik\Rfc9421\Signature\ComponentBuilder;
use Filefabrik\Rfc9421\StructuredFields\EntryWithValue;
use Filefabrik\Rfc9421\StructuredFields\MethodsMapper;
use Filefabrik\Rfc9421\StructuredFields\StructuredFields;

/**
 * Serial Field
 *
 * @see 2.1.2. Dictionary Structured Field Members
 */
final readonly class Sf
{
    public function __construct(
        private(set) MethodsMapper $methodsMapper,
        private(set) StructuredFields $structuredFields,
    ) {
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function handle(EntryWithValue $entry): EntryWithValue
    {
        $value = $this->processValue($entry);

        return EntryWithValue::fromBuilder(
            builder: ComponentBuilder::from($entry->getFieldName())
                                     ->withSf(),
            value  : $value,
        );
    }

    /**
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    private function processValue(EntryWithValue $entry): string
    {
        /**
         * Make sure the Header-Key is mapped in structured FieldTypes.
         * That tells, how we extract/process the value
         */
        $type = $this->structuredFields->getOrThrow($entry->getFieldName());

        /**
         * Calling/executing the field
         */
        $field = $this->methodsMapper->callMethod($type, [$entry->getValue()]);

        if (null === $field) {
            throw StructuredFieldException::unknownStructuredFieldType($entry->getFieldName());
        }

        /*
         * Working with Bakame Structured Fields
         */

        return array_any(
            [OuterList::class, InnerList::class, Parameters::class, Dictionary::class, Item::class],
            fn (string $className): bool => $field instanceof $className,
        )
            ? $field->toHttpValue()
            : $field;
    }
}
