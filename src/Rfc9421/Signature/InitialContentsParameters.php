<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signature;

use Bakame\Http\StructuredFields\Bytes;
use Bakame\Http\StructuredFields\DisplayString;
use Bakame\Http\StructuredFields\Token;
use DateTimeImmutable;
use Filefabrik\Rfc9421\Enums\InitialContentsEnum;
use Filefabrik\Rfc9421\Exceptions\UnexpectedValue;

/**
 * Todo check which combinations are allowed per rfc
 */
final class InitialContentsParameters
{
    /**
     * @var non-empty-array<string, Bytes|DisplayString|Token|bool|DateTimeImmutable|float|int|string|null >|array{}
     */
    private(set) array $parameters = [];

    /**
     * @param non-empty-array<string, Bytes|DisplayString|Token|bool|DateTimeImmutable|float|int|string|null >|array{} $parameters
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public function __construct(array $parameters = [])
    {
        $this->setParameters($parameters);
    }

    /**
     * @param non-empty-array<string, Bytes|DisplayString|Token|bool|DateTimeImmutable|float|int|string|null >|array{} $parameters
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
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public function setParameter(
        string|InitialContentsEnum $name,
        Bytes|DisplayString|Token|bool|DateTimeImmutable|float|int|string|null $value = null,
    ): self {
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
     * Set with InitialContentsEnum
     */
    public function setInitialContentsEnum(
        InitialContentsEnum $name,
        Bytes|DisplayString|Token|bool|DateTimeImmutable|float|int|string|null $value = null,
    ): self {
        $this->parameters[$name->name] = $value;

        return $this;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function getValueOrThrow(
        string|InitialContentsEnum $name,
    ): Bytes|DisplayString|Token|bool|DateTimeImmutable|float|int|string|null {
        $sName = $this->normalizeEnum($name);

        return $this->has($name) ?
            $this->parameters[$sName] :
            throw new UnexpectedValue(sprintf('Initial contents enum: "%s" was not set before.', $sName));
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public function has(InitialContentsEnum|string $name): bool
    {
        return array_key_exists($this->normalizeEnum($name), $this->parameters);
    }
}
