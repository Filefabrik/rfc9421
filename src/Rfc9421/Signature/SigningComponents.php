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

use Filefabrik\Rfc9421\Dto\ComponentItemDto;
use Filefabrik\Rfc9421\Enums\DerivedComponentEnum;
use Filefabrik\Rfc9421\Enums\InitialContentsEnum;
use Filefabrik\Rfc9421\Exceptions\AlreadySetException;

/**
 * List of all Components for a singing process
 */
final class SigningComponents
{
    /**
     * @var array{ComponentItemDto}|array{}
     */
    private array $components = [];

    /**
     * @return ComponentItemDto[]|array{}
     */
    public function getComponents(): array
    {
        return $this->components;
    }

    public function findComponentByName(string $name): ?ComponentItemDto
    {
        return array_find($this->components, fn ($component) => $component->fieldName === $name);
    }
    /**
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function addName(string|DerivedComponentEnum $name): self
    {
        return $this->set(ComponentName::fromComponentName($name));
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     */
    public function set(ComponentItemDto $component): self
    {
        if ($this->isset($component)) {
            throw AlreadySetException::ComponentExists($component->httpValue);
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
            fn (ComponentItemDto $setComponent): bool => $component->httpValue === $setComponent->httpValue,
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function addWithContentsEnum(string $name, string|InitialContentsEnum $initialContentsEnum): self
    {
        return $this->set(ComponentName::fromNameAndInitialContent($name, $initialContentsEnum));
    }

}
