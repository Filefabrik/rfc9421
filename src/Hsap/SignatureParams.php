<?php

declare(strict_types=1);

namespace Filefabrik\Hsap;

use Filefabrik\Hsap\Exceptions\AlreadySetException;
use Filefabrik\Hsap\Exceptions\InvalidDerivedKeyException;
use Filefabrik\Hsap\Exceptions\InvalidHeaderKeyException;

/**
 * Handles all relevant signatures for the header key "Signature-Input"
 */
final class SignatureParams
{
    private array $signatureParameters = [];

    /**
     * Regular header for the signature
     *
     * @throws InvalidHeaderKeyException
     * @throws AlreadySetException
     */
    public function setHeaderName(string $name): self
    {
        if (str_starts_with($name, '@')) {
            throw new InvalidHeaderKeyException('Regular Header keys for signature does not start with "@"');
        }

        return $this->addIfNotExists($name);
    }

    /**
     * Derived component header key
     *
     * @throws AlreadySetException
     */
    public function addIfNotExists(string $name): self
    {
        if ($this->exists($name)) {
            throw new AlreadySetException(
                sprintf('Signature-Input parameter "%s" was set before and can not be set again.', $name),
            );
        }
        $this->signatureParameters[] = $name;

        return $this;
    }

    /**
     * Check, already set before
     *
     */
    public function exists(string $name): bool
    {
        return in_array($name, $this->signatureParameters, true);
    }

    /**
     * Set a derived component key which has to be in the signature
     *
     * @throws InvalidDerivedKeyException
     * @throws AlreadySetException
     */
    public function setDerivedComponentName(string $derivedComponentName): self
    {
        // ensure leading @
        if (!str_starts_with($derivedComponentName, '@')) {
            throw new InvalidDerivedKeyException('Derived component name must start with "@"');
        }

        return $this->addIfNotExists($derivedComponentName);
    }

    /**
     * Getting the header parameter as a FiFo array. In this order the "signature-input" has to be written
     *
     * @return array<string|int|float>
     */
    public function getSignatureParameters(): array
    {
        return $this->signatureParameters;
    }
}
