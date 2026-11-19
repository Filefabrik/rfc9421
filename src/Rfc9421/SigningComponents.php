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
use Filefabrik\Rfc9421\Signature\ComponentEncoder;
use Filefabrik\Rfc9421\Signature\ComponentName;

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
     * @param array<int, ComponentItemDto|DerivedComponentEnum|ComponentEncoder|string> $headerKeys
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function signHeaders(array $headerKeys): self
    {
        foreach ($headerKeys as $headerKey) {
            $this->signHeader($headerKey);
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
    public function signHeader(DerivedComponentEnum|string|ComponentItemDto|ComponentEncoder $headerKey): self
    {
        if ($headerKey instanceof ComponentItemDto) {
            $this->set($headerKey);
        } elseif ($headerKey instanceof ComponentEncoder) {
            $this->set(ComponentItemDto::fromComponentEncoder($headerKey));
        } else {
            $this->set(ComponentName::fromString($headerKey));
        }

        return $this;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     */
    public function set(ComponentItemDto $component): self
    {
        if ($this->isset($component)) {
            throw AlreadySetException::ComponentExists($component->getHttpValue());
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
            fn (ComponentItemDto $setComponent): bool => $component->getHttpValue() === $setComponent->getHttpValue(),
        );
    }

    /**
     * Signing "$headerKey";bs
     * Signing "$headerKey";req;bs
     *
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function signWithBS(DerivedComponentEnum|string $headerKey, bool $asReq = false): self
    {
        return $this->set(ComponentName::withBs(name: $headerKey, asReq: $asReq));
    }

    /**
     * Signing "$headerKey";sf
     * Signing "$headerKey";req;sf
     *
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function signWithSF(DerivedComponentEnum|string $headerKey, bool $asReq = false): self
    {
        return $this->set(ComponentName::withSf(name: $headerKey, asReq: $asReq));
    }

    /**
     * Signing "$headerKey";key="$keyValue"
     * Signing "$headerKey";req;key="$keyValue"
     *
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function signWithKey(
        DerivedComponentEnum|string $headerKey,
        string $keyValue,
        bool $asReq = false,
    ): self {
        $this->set(
            ComponentName::withKey(
                name    : $headerKey,
                keyValue: $keyValue,
                asReq   : $asReq,
            ),
        );

        return $this;
    }

    /**
     * Signing "$headerKey";name="$nameValue"
     * Signing "$headerKey";req;name="$nameValue"
     *
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function signWithName(
        DerivedComponentEnum|string $headerKey,
        string $nameValue,
        bool $asReq = false,
    ): self {
        $this->set(
            ComponentName::withName(
                name     : $headerKey,
                nameValue: $nameValue,
                asReq    : $asReq,
            ),
        );

        return $this;
    }

    /**
     * Only the "component-name"
     *
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function sign(string|DerivedComponentEnum $headerKey, bool $asReq = false): self
    {
        return $this->set(
            ComponentName::withComponent(
                name : $headerKey,
                asReq: $asReq,
            ),
        );
    }

    /**
     * @return array{ComponentItemDto}|array{}
     *
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
            fn (ComponentItemDto $component): bool => $component->getHttpValue() === $name,
        );
    }

}
