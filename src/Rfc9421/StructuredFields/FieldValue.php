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

use Filefabrik\Rfc9421\Dto\ComponentItemDto;
use Filefabrik\Rfc9421\Dto\HeaderItemsDto;
use Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException;
use Filefabrik\Rfc9421\Signature\ComponentBuilder;
use Filefabrik\Rfc9421\Signature\InitialContentsParameters;
use Filefabrik\Rfc9421\SigningConfiguration;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * try to detect field with value
 * signature component: '"example-dict": sf' will be '"example-dict";sf: a=1, b=2;x=1;y=2, c=(a b c), d'
 * Header: 'Example-Dict'  => 'a=1,    b=2;x=1;y=2,   c=(a   b   c), d '
 */
final readonly class FieldValue
{
    public function __construct(
        private(set) RequestInterface|ResponseInterface $messageInterface,
        private(set) SigningConfiguration $signingConfiguration,
        private(set) HeaderItemsDto $headerItemsDto,
        private(set) ComponentItemDto $componentItemDto,
        private(set) InitialContentsParameters $initialContentsParameters,
    ) {
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function getFieldValue(): EntryWithValue
    {
        return $this->handleRequestInterface();
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    private function handleRequestInterface(): EntryWithValue
    {
        // first test the method is in the configuration stack
        // todo log later if there is no method. No Method is ok but maybe an configuration error
        $signingMethods = $this->signingConfiguration->getSigningMethodsWithDefaults();
        if ($signingMethods->hasMethod($this->componentItemDto->fieldName)) {
            return $signingMethods->callWith(
                fieldName: $this->componentItemDto->fieldName,
                // todo more bootable parameters
                params   : [
                    $this->messageInterface,
                    $this->initialContentsParameters,
                ],
            );
        }

        return $this->getDefaultEntry();
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    private function getDefaultEntry(): EntryWithValue
    {
        // todo header field not found
        // todo check why no parameter was set

        $fieldName = $this->componentItemDto->fieldName;
        if ($this->headerItemsDto->hasHeader(headerName: $fieldName)) {
            return EntryWithValue::fromBuilder(new ComponentBuilder($fieldName))
                                 ->setValue($this->headerItemsDto->getTrimmedHeaderValue($fieldName))
            ;
        }

        throw InvalidHeaderKeyException::headerNotExists($fieldName);
    }

}
