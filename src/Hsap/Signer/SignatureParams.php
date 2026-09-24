<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Signer;

use Filefabrik\Hsap\Exceptions\AlreadySetException;
use Filefabrik\Hsap\Exceptions\InvalidDerivedKeyException;
use Filefabrik\Hsap\Exceptions\InvalidHeaderKeyException;

/**
 * Handles all relevant signatures for the header key "Signature-Input"
 */
final class SignatureParams
{
    /**
     * This ordered array is important to collect the vars for signing and extracting
     *
     * @var string[]
     */
    private array $signatureParameters = [];

    /**
     * Set many components
     *
     * @throws InvalidDerivedKeyException
     * @throws InvalidHeaderKeyException
     * @throws AlreadySetException
     */
    public static function fromArray(array $params): self
    {
        return new self()->adds($params);
    }

    /**
     * @param array $keys
     *
     * @return $this
     * @throws AlreadySetException
     * @throws InvalidDerivedKeyException
     * @throws InvalidHeaderKeyException
     */
    public function adds(array $keys): self
    {
        foreach ($keys as $key) {
            $this->add($key);
        }

        return $this;
    }

    /**
     * Adding in the wild
     *
     * @throws InvalidDerivedKeyException
     * @throws InvalidHeaderKeyException
     * @throws AlreadySetException
     */
    public function add(string $key): self
    {
        return str_starts_with($key, '@') ? $this->setDerivedComponentName($key) : $this->setHeaderName($key);
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
     * Derived component header key
     *
     * @throws AlreadySetException
     */
    public function addIfNotExists(string $name): self
    {
        $name = strtolower($name);
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
     */
    public function exists(string $name): bool
    {
        $name = strtolower($name);

        return in_array($name, $this->signatureParameters, true);
    }

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
     * Getting the header parameter as a FiFo array. In this order the "signature-input" has to be written
     *
     * @return string[]
     */
    public function getSignatureParameters(): array
    {
        return $this->signatureParameters;
    }
}
