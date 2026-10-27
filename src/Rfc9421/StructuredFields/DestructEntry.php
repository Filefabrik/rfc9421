<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\StructuredFields;

use Bakame\Http\StructuredFields\Item;
use Filefabrik\Rfc9421\Dto\ComponentItemDto;
use Filefabrik\Rfc9421\Dto\HeaderItemsDto;
use Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException;
use Filefabrik\Rfc9421\Signature\InitialContentsEnum;
use Filefabrik\Rfc9421\Signature\InitialContentsParameters;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421\StructuredFields\Processing\Bs;
use Filefabrik\Rfc9421\StructuredFields\Processing\Key;
use Filefabrik\Rfc9421\StructuredFields\Processing\Sf;
use Filefabrik\Rfc9421\Support\HeaderItems;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class DestructEntry
{
    /**
     * Required only if we are handle a signed response where we want to use the req field
     */
    private(set) RequestInterface|null $originalRequest = null;



    public function __construct(
        private(set) readonly ComponentItemDto $componentItemDto,
        private(set) readonly SigningConfiguration $signingConfiguration,
        private(set) readonly HeaderItemsDto $headerItemsDto,
        private(set) readonly MessageInterface $messageInterface,
    ) {
    }

    /**
     *
     * @throws \Bakame\Http\StructuredFields\StructuredFieldError
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     */
    public function handle(): ?Entry
    {
        $parameters = $this->extractParameters($this->componentItemDto->getItem());

        // throws
        $this->checkParametersConflict($parameters);

        $whichHeaders = $this->prepareWhichHeaders($parameters);

        $entry = new FieldValue(
            signingConfiguration     : $this->signingConfiguration,
            initialContentsParameters: $parameters,
            headerItemsDto           : $whichHeaders,
            componentItemDto             : $this->componentItemDto,
            messageInterface         : $this->messageInterface,
        )->getFieldValue();

        /*
         * handle InitialContentsEnum part
         */
        if ($parameters->has(InitialContentsEnum::bs)) {
            $entry = new Bs()
                ->handle(
                    entry       : $entry,
                    whichRequest: $this->messageInterface,
                )
            ;
        } elseif ($parameters->has(InitialContentsEnum::sf)) {
            $entry = new Sf(
                methodsMapper       : $this->signingConfiguration->methodsMapper,
                structuredFieldTypes: $this->signingConfiguration->structuredFields,
            )->handle(
                entry: $entry,
            );
        } elseif ($parameters->has(InitialContentsEnum::key)) {
            $entry = new Key(
                methodsMapper       : $this->signingConfiguration->methodsMapper,
                structuredFieldTypes: $this->signingConfiguration->structuredFields,
            )->handle(
                entry    : $entry,
                childName: $parameters->getValueOrThrow(InitialContentsEnum::key),
            );
        }

        return $entry;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    private function extractParameters(Item $field): InitialContentsParameters
    {
        $parameters  = new InitialContentsParameters();
        $fieldParams = $field->parameters();

        if ($fieldParams->isNotEmpty()) {
            $indices = $fieldParams->indices();
            foreach ($indices as $index) {
                [$name, $item] = $fieldParams->getByIndex($index);
                $parameters->setParameter($name, $item->value());
            }
        }

        return $parameters;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     */
    private function checkParametersConflict(InitialContentsParameters $parameters): void
    {
        if ($parameters->has(InitialContentsEnum::bs) && $parameters->has(InitialContentsEnum::sf)) {
            throw new UnProcessableSignatureException(
                sprintf(
                    'Cannot use both "%s" and "%s"',
                    InitialContentsEnum::bs->name,
                    InitialContentsEnum::sf->name,
                ),
            );
        }
    }

    /**
     * Todo Method not tested, todo only during response signing
     *
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     */
    private function prepareWhichHeaders(InitialContentsParameters $parameters): HeaderItemsDto
    {
        $whichHeaders = $this->headerItemsDto;
        if ($parameters->has(InitialContentsEnum::req)) {
            if ($this->messageInterface instanceof ResponseInterface) {
                $whichRequest = $this->originalRequest;
                $whichHeaders = HeaderItemsDto::fromHeaderItems(HeaderItems::fromInterface($whichRequest));
            } else {
                throw new UnProcessableSignatureException('missing request for req parameter');
            }
        }

        // todo check tr value
        if ($parameters->has(InitialContentsEnum::tr)) {
            return $whichRequest->getTrailers();
        }

        return $whichHeaders;
    }

}
