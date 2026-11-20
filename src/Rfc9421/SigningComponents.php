<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */

declare(strict_types=1);

namespace Filefabrik\Rfc9421;

use Filefabrik\Rfc9421\Dto\ComponentItemDto;
use Filefabrik\Rfc9421\Enums\DerivedComponentEnum;
use Filefabrik\Rfc9421\Exceptions\AlreadySetException;
use Filefabrik\Rfc9421\Signature\ComponentBuilder;
use Filefabrik\Rfc9421\Signature\ComponentExtractor;

/**
 * Helper-Stack which components must be signed
 */
final class SigningComponents
{
    /**
     * @var array{ComponentItemDto}|array{}
     */
    private array $components = [];

    /**
     * Signing header Parameter
     * todo documentation
     *
     * @param array<int, ComponentItemDto|DerivedComponentEnum|ComponentBuilder|string> $fieldNames
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function signHeaders(array $fieldNames): self
    {
        foreach ($fieldNames as $fieldName) {
            $this->signHeader($fieldName);
        }

        return $this;
    }

    /**
     * Sign Component pure from header
     * todo, if the code /path?param=value&foo=bar&baz=batman&qux= and the signing wich
     * Todo set the sign header has some rules what is valid.
     *
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function signHeader(DerivedComponentEnum|string|ComponentItemDto|ComponentBuilder $fieldName): self
    {
        if ($fieldName instanceof ComponentItemDto) {
            $this->set($fieldName);
        } elseif ($fieldName instanceof ComponentBuilder) {
            $this->set(ComponentItemDto::fromComponentBuilder($fieldName));
        } else {
            // can be everything
            $this->set(ComponentExtractor::extractToComponentItemDto($fieldName));
        }

        return $this;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     */
    public function set(ComponentItemDto $component): self
    {
        if ($this->isset($component)) {
            throw AlreadySetException::ComponentExists($component->getComponent());
        }

        $this->components[] = $component;

        return $this;
    }

    /**
     * Does not reorder fully to string because of  '"example-dict"'  and '"example-dict";key="d"' they share the same 'example-dict' but have other values
     */
    private function isset(ComponentItemDto $component): bool
    {
        return array_any(
            $this->components,
            fn (ComponentItemDto $setComponent): bool => $component->getComponent() === $setComponent->getComponent(),
        );
    }

    /**
     * Signing "$fieldName";bs
     * Signing "$fieldName";req;bs
     *
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function signWithBS(DerivedComponentEnum|string $fieldName, bool $asReq = false): self
    {
        return $this->set(
            ComponentBuilder::from($fieldName)
                            ->withBs()
                            ->setReq($asReq)
                            ->toComponentItemDto(),
        );
    }

    /**
     * Signing "$fieldName";sf
     * Signing "$fieldName";req;sf
     *
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function signWithSF(DerivedComponentEnum|string $fieldName, bool $asReq = false): self
    {
        return $this->set(
            ComponentBuilder::from($fieldName)
                            ->withSf()
                            ->setReq($asReq)
                            ->toComponentItemDto(),
        );
    }

    /**
     * Signing "$fieldName";key="$keyValue"
     * Signing "$fieldName";req;key="$keyValue"
     *
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function signWithKey(
        DerivedComponentEnum|string $fieldName,
        string $keyValue,
        bool $asReq = false,
    ): self {
        $this->set(
            ComponentBuilder::from($fieldName)
                            ->withKey($keyValue)
                            ->setReq($asReq)
                            ->toComponentItemDto(),
        );

        return $this;
    }

    /**
     * Signing "$fieldName";fieldName="$nameValue"
     * Signing "$fieldName";req;fieldName="$nameValue"
     *
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function signWithName(
        DerivedComponentEnum|string $fieldName,
        string $nameValue,
        bool $asReq = false,
    ): self {
        $this->set(
            ComponentBuilder::from($fieldName)
                            ->withName($nameValue)
                            ->setReq($asReq)
                            ->toComponentItemDto(),
        );

        return $this;
    }

    /**
     * Only the "component-fieldName"
     *
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function sign(string|DerivedComponentEnum $fieldName, bool $asReq = false): self
    {
        return $this->set(
            ComponentBuilder::from($fieldName)
                            ->setReq($asReq)
                            ->toComponentItemDto(),
        );
    }

    /**
     * @return array{ComponentItemDto}|array{}
     */
    public function getComponents(): array
    {
        return $this->components;
    }

    /**
     * @param string $name with full line "some";key="d";
     *
     */
    public function findComponentByName(string $name): ?ComponentItemDto
    {
        return array_find(
            $this->components,
            fn (ComponentItemDto $component): bool => $component->getComponent() === $name,
        );
    }

}
