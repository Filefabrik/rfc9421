<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\StructuredFields;

use Bakame\Http\StructuredFields\Item;
use Filefabrik\Rfc9421\Dto\ComponentItemDto;
use Filefabrik\Rfc9421\Dto\HeaderItemsDto;
use Filefabrik\Rfc9421\Enums\InitialContentsEnum;
use Filefabrik\Rfc9421\Signature\InitialContentsParameters;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421\StructuredFields\Processing\Bs;
use Filefabrik\Rfc9421\StructuredFields\Processing\Key;
use Filefabrik\Rfc9421\StructuredFields\Processing\Name;
use Filefabrik\Rfc9421\StructuredFields\Processing\Sf;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Create the Signing Key Value
 */
final readonly class CompileEntry
{
    /* todo
     * Required only if we are handle a signed response where we want to use the req field

       private(set) RequestInterface|null $originalRequest = null;
    */
    public function __construct(
        private(set) RequestInterface|ResponseInterface $messageInterface,
        private(set) SigningConfiguration $signingConfiguration,
        private(set) ComponentItemDto $componentItemDto,
        private(set) HeaderItemsDto $headerItemsDto,
    ) {
    }

    /**
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     */
    public function handle(): Entry
    {
        $parameters = $this->extractParameters($this->componentItemDto->getItem());

        // todo check parameters duplicated is allowed or not in RFC
        // throws
        //  $this->checkParametersConflict($parameters);
        /*
        todo implement trailer field
        if ($parameters->has(InitialContentsEnum::tr)) {
              return $responseInterface->getTrailers();
          }*/

        $entry = new FieldValue(
            messageInterface         : $this->messageInterface,
            signingConfiguration     : $this->signingConfiguration,
            headerItemsDto           : $this->headerItemsDto,
            componentItemDto         : $this->componentItemDto,
            initialContentsParameters: $parameters,
        )->getFieldValue();

        /*
         * todo maybe it will be good to handle the parameter-processing classes via a closures
         * handle InitialContentsEnum part
         */
        if ($parameters->has(InitialContentsEnum::bs)) {
            $entry = new Bs()
                ->handle(
                    entry           : $entry,
                    messageInterface: $this->messageInterface,
                )
            ;
        } elseif ($parameters->has(InitialContentsEnum::sf)) {
            $entry = new Sf(
                methodsMapper   : $this->signingConfiguration->getMethodsMapper(),
                structuredFields: $this->signingConfiguration->getStructuredFields(),
            )->handle(
                entry: $entry,
            );
        } elseif ($parameters->has(InitialContentsEnum::key)) {
            // todo intense testing
            $entry = new Key(
                structuredFieldTypes: $this->signingConfiguration->getStructuredFields(),
            )->handle(
                entry  : $entry,
                keyName: $parameters->getValueOrThrow(InitialContentsEnum::key),
            );
        } elseif ($parameters->has(InitialContentsEnum::name)) {
            // todo testing
            $entry = new Name()
                ->handle(
                    entry  : $entry,
                    keyName: $parameters->getValueOrThrow(InitialContentsEnum::name),
                )
            ;
        }

        /*
         * todo ;req only during response signing a request part
         */

        return $entry;
    }

    /**
     * From Bakame Field Item Parameter into Filefabrik InitialContents Parameters
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    private function extractParameters(Item $field): InitialContentsParameters
    {
        $parameters  = new InitialContentsParameters();
        $fieldParams = $field->parameters();

        if ($fieldParams->isNotEmpty()) {
            foreach ($fieldParams as $param) {
                $parameters->setParameter($param[0], $param[1]->value());
            }
        }

        return $parameters;
    }

}
