<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\StructuredFields;

use Bakame\Http\StructuredFields\Item;
use Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException;
use Filefabrik\Rfc9421\Methods\NormalizedAuthority;
use Filefabrik\Rfc9421\Methods\QueryParams;
use Filefabrik\Rfc9421\Methods\Scheme;
use Filefabrik\Rfc9421\Methods\TargetUri;
use Filefabrik\Rfc9421\Signature\DerivedComponentEnum;
use Filefabrik\Rfc9421\Signature\InitialContentsEnum;
use Filefabrik\Rfc9421\Signature\InitialContentsParameters;
use Filefabrik\Rfc9421\StructuredFields\Processing\Bs;
use Filefabrik\Rfc9421\StructuredFields\Processing\Key;
use Filefabrik\Rfc9421\StructuredFields\Processing\Sf;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use UnexpectedValueException;

final class DestructEntry
{
    public $originalRequest;
    public function __construct(
        private(set) Item $fieldItem,
        private(set) readonly MethodsMapper $methodsMapper,
        private(set) readonly StructuredFields $structuredFields,
    ) {
    }

    /**
     * @throws \Bakame\Http\StructuredFields\StructuredFieldError
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public function canonicalizeComponent(array $headers, MessageInterface $interface): Entry
    {

        $fieldName = $this->getFieldName($this->fieldItem);

        $parameters = $this->extractParameters($this->fieldItem);

        // throws
        $this->checkParametersConflict($parameters);

        $whichRequest = $interface;
        $whichHeaders = $this->prepareWhichHeaders($headers, $interface, $parameters);

        // todo do not use the name from here
        $entry = $this->getFieldValue(
            fieldName : $fieldName,
            interface : $whichRequest,
            headers   : $whichHeaders,
            parameters: $parameters
        );

        /*
         * handle InitialContentsEnum part
         */
        if ($parameters->has(InitialContentsEnum::bs)) {
            return new Bs()->handle($entry, $whichRequest);
        }

        if ($parameters->has(InitialContentsEnum::sf)) {
            return new Sf(methodsMapper: $this->methodsMapper, structuredFieldTypes: $this->structuredFields)->handle(
                entry: $entry
            );
        }

        if ($parameters->has(InitialContentsEnum::key)) {
            return new Key(methodsMapper: $this->methodsMapper, structuredFieldTypes: $this->structuredFields)->handle(
                entry    : $entry,
                childName: $parameters->getValueOrThrow(InitialContentsEnum::key),
            );
        }

        return $entry;
    }

    private function getFieldName(Item $item): string
    {
        // cast the fieldName to string
        return (string)$item->value(fn ($value): bool => is_string($value));
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
     * todo with trailer
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
                $whichHeaders = self::getLowerCasedHeadersFromInterface($whichRequest);
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

    /**
     * @param array<string,string> $headers
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    private function getFieldValue(
        string $fieldName,
        MessageInterface $interface,
        array $headers,
        InitialContentsParameters $parameters,
    ): Entry {
        $derivedComponentOrNull = DerivedComponentEnum::isOwnValueOrNull($fieldName);
        if ($interface instanceof RequestInterface) {
            $entry = match ($derivedComponentOrNull) {
                DerivedComponentEnum::signatureParams => throw new UnexpectedValueException(
                    'remove before processing signatureParams',
                ),
                DerivedComponentEnum::method          =>
                new Entry(
                    DerivedComponentEnum::method->value,
                    strtoupper($interface->getMethod()),
                ),
                DerivedComponentEnum::authority       =>
                new Entry(
                    DerivedComponentEnum::authority->value,
                    NormalizedAuthority::handle($interface),
                ),
                DerivedComponentEnum::scheme          =>
                new Entry(
                    DerivedComponentEnum::scheme->value,
                    Scheme::handle($interface),
                ),
                DerivedComponentEnum::targetUri       => new Entry(
                    DerivedComponentEnum::targetUri->value,
                    TargetUri::handle($interface),
                )
                ,
                DerivedComponentEnum::requestTarget   =>
                new Entry(
                    DerivedComponentEnum::requestTarget->value,
                    $interface->getRequestTarget(),
                ),
                DerivedComponentEnum::path            =>
                new Entry(
                    DerivedComponentEnum::path->value,
                    $interface->getUri()
                              ->getPath(),
                )
                ,

                DerivedComponentEnum::query           =>
                new Entry(
                    DerivedComponentEnum::query->value,
                    $interface->getUri()
                              ->getQuery(),
                ),
                DerivedComponentEnum::queryParam      =>
                QueryParams::handle(
                    $interface,
                    $parameters->getValueOrThrow(InitialContentsEnum::name),
                ),
                // requires Entry generation

                default                               => new Entry($fieldName, trim($headers[$fieldName] ?? ''))
            };
        } else {
            $entry = match ($derivedComponentOrNull) {
                DerivedComponentEnum::signatureParams => throw new UnexpectedValueException(
                    'remove before processing signatureParams',
                ),
                DerivedComponentEnum::status          =>
                new Entry(
                    DerivedComponentEnum::status->value,
                    $interface->getStatusCode(),
                ),

                default                               => new Entry($fieldName, trim($headers[$fieldName] ?? '')),
            };
        }

        // todo req is only attachable if others not set
        if ($parameters->has(InitialContentsEnum::req)) {
            $entry->withREQEnum();
            // $entry[0] .= ';'.InitialContentsEnum::req->name;
        }

        return $entry;
    }

}
