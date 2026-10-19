<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\StructuredFields;

use Filefabrik\Rfc9421\Signature\InitialContentsEnum;
use Filefabrik\Rfc9421\Signature\InitialContentsParameters;
use Filefabrik\Rfc9421\SigningConfiguration;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use UnexpectedValueException;

final readonly class FieldValue
{
    /**
     * @param array<string,string> $lowerCasedHeaders
     */
    public function __construct(
        private(set) SigningConfiguration $signingConfiguration,
        private(set) InitialContentsParameters $initialContentsParameters,
        private(set) array $lowerCasedHeaders,
        private(set) string $fieldName,
        private(set) MessageInterface $messageInterface,
    ) {
    }

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

    private function handleRequestInterface(): Entry
    {
        // first test the method is in the configuration stack

        if ($this->signingConfiguration->signingMethods?->hasMethod($this->fieldName) ?? false) {
            $entry = $this->signingConfiguration->signingMethods->callWith(
                $this->fieldName,
                $this->messageInterface,
                $this->initialContentsParameters,
            );
        } else {
            $entry = $this->getDefaultEntry();
        }

        return $this->handleREQ($entry);
    }

    private function getDefaultEntry(): Entry
    {
        return new Entry($this->fieldName, trim($this->lowerCasedHeaders[$this->fieldName] ?? ''));
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

    private function handleResponseInterface(): Entry
    {
        if ($this->signingConfiguration->signingMethods?->hasMethod($this->fieldName) ?? false) {
            $entry = $this->signingConfiguration->signingMethods->callWith(
                $this->fieldName,
                $this->messageInterface,
                $this->initialContentsParameters,
            );
        } else {
            $entry = $this->getDefaultEntry();
        }

        return $this->handleREQ($entry);
    }
}
