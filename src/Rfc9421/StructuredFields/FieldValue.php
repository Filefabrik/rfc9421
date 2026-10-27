<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\StructuredFields;

use Filefabrik\Rfc9421\Dto\ComponentItemDto;
use Filefabrik\Rfc9421\Dto\HeaderItemsDto;
use Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException;
use Filefabrik\Rfc9421\Signature\InitialContentsEnum;
use Filefabrik\Rfc9421\Signature\InitialContentsParameters;
use Filefabrik\Rfc9421\SigningConfiguration;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use UnexpectedValueException;

/**
 * try to detect field with value
 */
final readonly class FieldValue
{
    public function __construct(
        private(set) SigningConfiguration $signingConfiguration,
        private(set) InitialContentsParameters $initialContentsParameters,
        private(set) HeaderItemsDto $headerItemsDto,
        private(set) ComponentItemDto $componentItemDto,
        private(set) MessageInterface $messageInterface,
    ) {
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public function getFieldValue(): Entry
    {
        if ($this->messageInterface instanceof RequestInterface) {
            return $this->handleRequestInterface();
        }

        if ($this->messageInterface instanceof ResponseInterface) {
            return $this->handleResponseInterface();
        }

        throw new UnexpectedValueException(
            sprintf('Unexpected message interface "%s"', $this->messageInterface::class),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    private function handleRequestInterface(): Entry
    {
        // first test the method is in the configuration stack

        if ($this->signingConfiguration->signingMethods?->hasMethod($this->componentItemDto->fieldName) ?? false) {

            $entry = $this->signingConfiguration->signingMethods->callWith(
                $this->componentItemDto->fieldName,
                $this->messageInterface,
                $this->initialContentsParameters,
            );
        } else {
            $entry = $this->getDefaultEntry();
        }

        return $this->handleREQ($entry);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     */
    private function getDefaultEntry(): Entry
    {
        // todo header field not found

        $fieldName = $this->componentItemDto->fieldName;
        if ($this->headerItemsDto->hasHeader($fieldName)) {
            return new Entry($fieldName, $this->headerItemsDto->getTrimmedHeader($fieldName));
        }

        throw InvalidHeaderKeyException::headerNotExists($fieldName);
    }

    /**
     * Todo req only on response signing
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    private function handleREQ(Entry $entry): Entry
    {
        // todo req is only attachable if others not set
        if ($this->initialContentsParameters->has(InitialContentsEnum::req)) {
            $entry->withREQEnum();
        }

        return $entry;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    private function handleResponseInterface(): Entry
    {
        $fieldName = $this->componentItemDto->fieldName;
        if ($this->signingConfiguration->signingMethods?->hasMethod($fieldName) ?? false) {
            $entry = $this->signingConfiguration->signingMethods->callWith(
                $fieldName,
                $this->messageInterface,
                $this->initialContentsParameters,
            );
        } else {
            $entry = $this->getDefaultEntry();
        }

        return $this->handleREQ($entry);
    }
}
