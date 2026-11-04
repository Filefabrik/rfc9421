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

use Bakame\Http\StructuredFields\Dictionary;
use Bakame\Http\StructuredFields\InnerList;
use Bakame\Http\StructuredFields\Item;
use Bakame\Http\StructuredFields\OuterList;
use Bakame\Http\StructuredFields\Parameters;
use Filefabrik\Rfc9421\Enums\InitialContentsEnum;
use Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException;
use Filefabrik\Rfc9421\StructuredFields\Entry;
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
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     */
    public function handle(Entry $entry): Entry
    {
        $value = $this->processValue($entry);

        return new Entry($entry->key, $value, InitialContentsEnum::sf);
    }

    /**
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     */
    private function processValue(Entry $entry): string
    {
        /**
         * Make sure the Header-Key is mapped in structured FieldTypes.
         * That tells, how we extract/process the value
         */
        $type = $this->structuredFields->getOrThrow($entry->key);

        /**
         * Calling/executing the field
         */
        $field = $this->methodsMapper->callMethod($type, [$entry->value]);

        if (null === $field) {
            throw new UnProcessableSignatureException(
                sprintf('Unknown or unregistered structured field type for "%s"', $entry->key),
            );
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
