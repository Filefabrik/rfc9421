<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\StructuredFields;

use Bakame\Http\StructuredFields\Item;
use Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException;
use Filefabrik\Rfc9421\Signature\InitialContentsEnum;
use Filefabrik\Rfc9421\Signature\InitialContentsParameters;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421\StructuredFields\Processing\Bs;
use Filefabrik\Rfc9421\StructuredFields\Processing\Key;
use Filefabrik\Rfc9421\StructuredFields\Processing\Sf;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class DestructEntry
{
    public $originalRequest;

    public function __construct(
        private(set) readonly Item $fieldItem,
        private(set) readonly MethodsMapper $methodsMapper,
        private(set) readonly StructuredFields $structuredFields,
        private(set) readonly SigningConfiguration $signingConfiguration,
    ) {
    }

    /**
     * @param array<string,string> $headers
     *
     * @throws \Bakame\Http\StructuredFields\StructuredFieldError
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public function canonicalizeComponent(array $headers, MessageInterface $interface): ?Entry
    {
        $fieldName = (string)$this->fieldItem->value(fn ($value): bool => is_string($value));

        $parameters = $this->extractParameters($this->fieldItem);

        // throws
        $this->checkParametersConflict($parameters);

        $whichRequest = $interface;
        $whichHeaders = $this->prepareWhichHeaders($headers, $interface, $parameters);

        $entry = new FieldValue(
            signingConfiguration     : $this->signingConfiguration,
            initialContentsParameters: $parameters,
        )->getFieldValue(
            fieldName       : $fieldName,
            messageInterface: $whichRequest,
            headers         : $whichHeaders,
        );

        /*
         * handle InitialContentsEnum part
         */
        if ($parameters->has(InitialContentsEnum::bs)) {
            $entry = new Bs()
                ->handle(
                    entry       : $entry,
                    whichRequest: $whichRequest,
                )
            ;
        } elseif ($parameters->has(InitialContentsEnum::sf)) {
            $entry = new Sf(
                methodsMapper       : $this->methodsMapper,
                structuredFieldTypes: $this->structuredFields,
            )->handle(
                entry: $entry,
            );
        } elseif ($parameters->has(InitialContentsEnum::key)) {
            $entry = new Key(
                methodsMapper       : $this->methodsMapper,
                structuredFieldTypes: $this->structuredFields,
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
     * @param array<string,string> $headers
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     */
    private function prepareWhichHeaders(
        array $headers,
        MessageInterface|RequestInterface|ResponseInterface $interface,
        InitialContentsParameters $parameters,
    ): array {
        $whichRequest = $interface;
        $whichHeaders = $headers;
        if ($parameters->has(InitialContentsEnum::req)) {
            if ($interface instanceof ResponseInterface) {
                $whichRequest = $this->originalRequest;
                $whichHeaders = DestructHeaders::getLowerCasedHeadersFromInterface($whichRequest);
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
