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

final class FieldValue
{
    public function __construct(
        private(set) SigningConfiguration $signingConfiguration,
        private(set) InitialContentsParameters $initialContentsParameters,
    ) {
    }

    /**
     * @param array<string,string> $headers
     *
     */
    public function getFieldValue(
        string $fieldName,
        MessageInterface $messageInterface,
        array $headers,
      
    ): Entry {
        if ($messageInterface instanceof RequestInterface) {
            return $this->handleRequestInterface($fieldName, $messageInterface,  $headers);
        }
        if ($messageInterface instanceof ResponseInterface) {
            return $this->handleResponseInterface($fieldName, $messageInterface, $headers);
        }
        throw new UnexpectedValueException(sprintf('Unexpected message interface "%s"', get_class($messageInterface)));
    }

    private function handleRequestInterface(
        string $fieldName,
        RequestInterface $requestInterface,

        $headers,
    ): Entry {
        // first test the method is in the configuration stack

        if ($this->signingConfiguration->signingMethods?->hasMethod($fieldName) ?? false) {
            $entry = $this->signingConfiguration->signingMethods->getMethod($fieldName)
                                                                ->call($this, $requestInterface, $this->initialContentsParameters)
            ;
        } else {
            $entry = new Entry($fieldName, trim($headers[$fieldName] ?? ''));
        }

        return $this->handleREQ($entry);
    }

    /**
     * Todo req only on response signing
     *
     *
     */
    private function handleREQ($entry)
    {
        // todo req is only attachable if others not set
        if ($this->initialContentsParameters->has(InitialContentsEnum::req)) {
            $entry->withREQEnum();
            // $entry[0] .= ';'.InitialContentsEnum::req->name;
        }

        return $entry;
    }

    private function handleResponseInterface(
        string $fieldName,
        ResponseInterface $response,

        $headers,
    ): Entry {
        if ($this->signingConfiguration->signingMethods?->hasMethod($fieldName) ?? false) {
            $entry = $this->signingConfiguration->signingMethods->getMethod($fieldName)
                                                                ->call($this, $response, $this->initialContentsParameters)
            ;
        } else {
            $entry = new Entry($fieldName, trim($headers[$fieldName] ?? ''));
        }

        return $this->handleREQ($entry);
    }
}
