<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signature;

use UnexpectedValueException;

/**
 * Todo check which combinations are allowed per rfc
 */
final class InitialContentsParameters
{
    /**
     * @var non-empty-array<string, string|int|null>|array{}
     */
    private(set) array $parameters = [];

    /**
     * @param non-empty-array<string, string|int|null>|array{} $parameters
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public function __construct(array $parameters = [])
    {
        $this->setParameters($parameters);
    }

    /**
     * @param non-empty-array<string, string|int|null>|array{} $parameters
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    private function setParameters(array $parameters): void
    {
        foreach ($parameters as $name => $value) {
            $this->setParameter($name, $value);
        }
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public function setParameter(string|InitialContentsEnum $name, string|int|null $value = null): self
    {
        $this->parameters[$this->normalizeEnum($name)] = $value;

        return $this;
    }

    /**
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    private function normalizeEnum(string|InitialContentsEnum $name): string
    {
        return InitialContentsEnum::isOwnTypeOrThrow($name)->name;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public function getValueOrThrow(string|InitialContentsEnum $name): int|string|null
    {
        $sName = $this->normalizeEnum($name);

        return $this->has($name) ?
            $this->parameters[$sName] :
            throw new UnexpectedValueException(sprintf('Initial contents enum: "%s" was not set before.', $sName));
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public function has(InitialContentsEnum|string $name): bool
    {
        return array_key_exists($this->normalizeEnum($name), $this->parameters);
    }
}
