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

use Exception;
use Filefabrik\Rfc9421\Dto\ComponentItemDto;
use Filefabrik\Rfc9421\Enums\DerivedComponentEnum;
use Filefabrik\Rfc9421\Enums\InitialContentsEnum;
use Filefabrik\Rfc9421\Exceptions\EnumTypeException;
use Filefabrik\Rfc9421\Exceptions\UnexpectedValue;
use Filefabrik\Rfc9421\StructuredFields\Entry;
use Filefabrik\Rfc9421\Support\NotSet;
use Filefabrik\Rfc9421\Support\UnquoteHelper;

/**
 * new variant to make from separate initialContentsParameters a component name
 */
final class ComponentBuilder implements \Stringable
{
    /**
     * Component-Name without quotas
     *
     */
    private readonly string|DerivedComponentEnum $name;

    private readonly InitialContentsParameters $initialContentsParameters;

    /**
     * They need a value later in name="expect_value" key="expect_value"
     *
     */
    private array $withValues = [InitialContentsEnum::key, InitialContentsEnum::name];

    /**
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function __construct(string|DerivedComponentEnum $name)
    {
        $this->name                      =
            $name instanceof DerivedComponentEnum
                ? $name :
                $this->setValidatedName($name);
        $this->initialContentsParameters = new InitialContentsParameters();
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    private function setValidatedName(string $name): string|DerivedComponentEnum
    {
        // todo validate value against unwanted chars/expressions
        $lower = strtolower(trim($name));

        // todo fine-style this setter

        if (str_starts_with($lower, '@')) {
            return DerivedComponentEnum::isOwnValueOrNull($lower) ??
                throw EnumTypeException::TypeError(
                    $lower,
                    DerivedComponentEnum::class,
                );
        }

        return UnquoteHelper::isQuoted($lower) ? UnquoteHelper::unquote($lower) : $lower;
    }

    /**
     * Helper if req should be set by flag
     */
    public function setReq(bool|null $flag = null): self
    {
        if ($flag) {
            $this->withReq();
        }

        return $this;
    }

    public function withReq(): self
    {
        return $this->setPair(InitialContentsEnum::req, new NotSet());
    }

    private function setPair(InitialContentsEnum $initialContentsEnum, \Filefabrik\Rfc9421\Support\NotSet|string $value): self
    {
        if ($value instanceof NotSet) {
            $this->initialContentsParameters->setInitialContentsEnum($initialContentsEnum);
        } else {
            $this->initialContentsParameters->setInitialContentsEnum($initialContentsEnum, $value);
        }

        return $this;
    }

    /**
     * Without the colon
     *
     */
    public function __toString(): string
    { /*
         * @see https://www.rfc-editor.org/info/rfc9421/#section-2.1.3
         * todo bs allowed with some byte sequences (base_encode64)
         * Example-Header: value, with, lots
         * Example-Header: of, commas
         * "example-header";bs: :dmFsdWUsIHdpdGgsIGxvdHM=:, :b2YsIGNvbW1hcw==:
         */
        /*
         * @see https://www.rfc-editor.org/info/rfc9421/#section-2.1.1-8
         * "However, if the sf parameter is added, the value is reserialized as follows:"
         * "example-dict";sf: a=1, b=2;x=1;y=2, c=(a b c)
         * "example-header";bs: :dmFsdWUsIHdpdGgsIGxvdHMsIG9mLCBjb21tYXM=:
         */
        $parts = [];

        // the key quoted
        $parts[] = $this->getQuotedName();

        $flagsArray = $this->renderFlags();

        if (0 < count($flagsArray)) {
            $parts[] = Entry::semicolon;
            $parts[] = implode(Entry::semicolon, $flagsArray);
        }

        return implode('', $parts);
    }

    /**
     * From pure name content-length to "content-length"
     */
    private function getQuotedName(): string
    {
        return UnquoteHelper::quote($this->name instanceof DerivedComponentEnum ? $this->name->value : $this->name);
    }

    private function renderFlags(): array
    {
        $parts = [];
        foreach ($this->initialContentsParameters->parameters as $key => $value) {
            /** @var InitialContentsEnum $enum */
            $enum = InitialContentsEnum::isOwnTypeOrNull($key);
            if (in_array($enum, $this->withValues, true)) {
                $parts[] = implode('', $this->handleKeyOrName($enum, $value));
            } else {
                $parts[] = $enum->name;
            }
        }

        return $parts;
    }

    /**
     * Special Method which is adding the `name="$this->nameOrKeyValue"` or `key="$this->nameOrKeyValue"`
     *
     *
     * @return string[]
     */
    private function handleKeyOrName(InitialContentsEnum $initialContentsEnum, string $keyOrValue): array
    {
        return [$initialContentsEnum->name, Entry::equals, UnquoteHelper::quote($keyOrValue)];
    }

    /**
     * @throws Exception
     */
    public function setDetect(InitialContentsEnum $contentsEnum, null|string $value = null): self
    {
        match ($contentsEnum) {
            // value
            InitialContentsEnum::key,
            InitialContentsEnum::name => $this->setWithValue($contentsEnum, $value),
            InitialContentsEnum::bs,
            InitialContentsEnum::req,
            InitialContentsEnum::sf   => $this->setValueless($contentsEnum),
        };

        return $this;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function setWithValue(InitialContentsEnum $initialContentsEnum, string|null $value = null): self
    {
        // todo check other key formats they are invalid
        if (null === $value || '' === $value) {
            throw new UnexpectedValue('Value can not be empty');
        }

        match ($initialContentsEnum) {
            InitialContentsEnum::key  => $this->withKey($value),
            InitialContentsEnum::name => $this->withName($value),
            default                   => throw new UnexpectedValue(
                sprintf('"%s" can not be set with an value', $initialContentsEnum->name),
            ),
        };

        return $this;
    }

    public function withKey(string $value): self
    {
        return $this->setPair(InitialContentsEnum::key, $value);
    }

    public function withName(string $value): self
    {
        return $this->setPair(InitialContentsEnum::name, $value);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function setValueless(InitialContentsEnum $contentsEnum): self
    {
        match ($contentsEnum) {
            InitialContentsEnum::bs  => $this->withBs(),
            InitialContentsEnum::sf  => $this->withSf(),
            InitialContentsEnum::req => $this->withReq(),
            default                  => throw new UnexpectedValue(
                sprintf('"%s" is not part of valueless.', $contentsEnum->name),
            ),
        };

        return $this;
    }

    public function withBs(): self
    {
        return $this->setPair(InitialContentsEnum::bs, new NotSet());
    }

    public function withSf(): self
    {
        return $this->setPair(InitialContentsEnum::sf, new NotSet());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function toComponentItemDto(): ComponentItemDto
    {
        return ComponentItemDto::fromComponentBuilder($this);
    }

}
