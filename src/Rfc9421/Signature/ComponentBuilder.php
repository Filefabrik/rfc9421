<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */

/* @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signature;

use Exception;
use Filefabrik\Rfc9421\Dto\ComponentItemDto;
use Filefabrik\Rfc9421\Enums\DerivedComponentEnum;
use Filefabrik\Rfc9421\Enums\InitialContentsEnum;
use Filefabrik\Rfc9421\Exceptions\EnumTypeException;
use Filefabrik\Rfc9421\Exceptions\UnexpectedValue;
use Filefabrik\Rfc9421\Interfaces\HeaderKeysEnumInterface;
use Filefabrik\Rfc9421\Support\UnquoteHelper;
use Stringable;

/**
 * new variant to make from separate initialContentsParameters a component fieldName
 */
final readonly class ComponentBuilder implements Stringable
{
    public const string semicolon = ';';

    public const string equals = '=';

    /**
     * Component-Name without quotas
     *
     */
    private(set) string|DerivedComponentEnum|HeaderKeysEnumInterface $fieldName;

    private InitialContentsParameters $initialContentsParameters;

    /**
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function __construct(string|DerivedComponentEnum|HeaderKeysEnumInterface $name)
    {
        $this->fieldName                 =
            $name instanceof DerivedComponentEnum || $name instanceof HeaderKeysEnumInterface ?
                $name :
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
     * Simply from a string without InitialContentsEnums or DerivedComponentEnum
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    public static function from(string|DerivedComponentEnum|HeaderKeysEnumInterface $name): self
    {
        return new self($name);
    }

    /**
     * Without the colon
     *
     */
    public function __toString(): string
    {
        /*
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

        $parametersArray = $this->renderParameters();

        if (0 < count($parametersArray)) {
            $parts[] = self::semicolon;
            $parts[] = implode(self::semicolon, $parametersArray);
        }

        return implode('', $parts);
    }

    /**
     * From pure fieldName content-length to "content-length"
     */
    private function getQuotedName(): string
    {
        return UnquoteHelper::quote($this->getFieldNameString());
    }

    /**
     * DerivedComponent value or string value without Quotas
     */
    public function getFieldNameString(): string
    {
        return $this->fieldName instanceof DerivedComponentEnum || $this->fieldName instanceof HeaderKeysEnumInterface ?
            $this->fieldName->value : $this->fieldName;
    }

    private function renderParameters(): array
    {
        return new RenderInitialContents(
            initialContentsParameters: $this->initialContentsParameters,
        )->toArray();
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
        return $this->internalSetWithValue(InitialContentsEnum::key, $value);
    }

    private function internalSetWithValue(InitialContentsEnum $initialContentsEnum, string $value): self
    {
        $this->initialContentsParameters->setInitialContentsEnum($initialContentsEnum, $value);

        return $this;
    }

    public function withName(string $value): self
    {
        return $this->internalSetWithValue(InitialContentsEnum::name, $value);
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
        return $this->setWithoutValue(InitialContentsEnum::bs);
    }

    private function setWithoutValue(InitialContentsEnum $initialContentsEnum): self
    {
        $this->initialContentsParameters->setInitialContentsEnum($initialContentsEnum);

        return $this;
    }

    public function withSf(): self
    {
        return $this->setWithoutValue(InitialContentsEnum::sf);
    }

    public function withReq(): self
    {
        return $this->setWithoutValue(InitialContentsEnum::req);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function toComponentItemDto(): ComponentItemDto
    {
        return ComponentItemDto::fromComponentBuilder($this);
    }

    /**
     * Helper if req should be set by flag
     */
    public function setReq(bool|null $flag = null): self
    {
        if (true === $flag) {
            $this->withReq();
        }

        return $this;
    }

}
