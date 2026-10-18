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
     * @param non-empty-array<string, mixed>|array{} $parameters
     */
    public function __construct(private(set) array $parameters = [])
    {
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public function setParameter(string|InitialContentsEnum $name, mixed $value): self
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
    public function getValueOrThrow(string|InitialContentsEnum $name): mixed
    {
        return $this->has($name) ? $this->parameters[$this->normalizeEnum($name)] :
            throw new UnexpectedValueException('Missing initial contents enum');
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public function has(InitialContentsEnum|string $name): bool
    {
        return array_key_exists($this->normalizeEnum($name), $this->parameters);
    }
}
