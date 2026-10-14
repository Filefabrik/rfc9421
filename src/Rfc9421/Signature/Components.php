<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signature;

use Filefabrik\Rfc9421\Exceptions\AlreadySetException;

/**
 * List of all Components for a singing process
 */
final class Components
{
    /**
     * @var array{\Filefabrik\Rfc9421\Signature\Component}|array{}
     */
    private array $components = [];

    /**
     * @return array{\Filefabrik\Rfc9421\Signature\Component}|array{}
     */
    public function getComponents(): array
    {
        return $this->components;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     */
    public function setByName(string $keyname, null|string|InitialContentsEnum $initialContentsEnum = null): self
    {
        return $this->set(component: new Component(name: $keyname, initialContentsEnum: $initialContentsEnum));
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     */
    public function set(Component $component): self
    {
        if ($this->isset($component)) {
            throw AlreadySetException::ComponentExists($component->name);
        }

        $this->components[] = $component;

        return $this;
    }

    /**
     * Does not reorder
     */
    private function isset(Component $component): bool
    {
        return array_any($this->components, fn ($setComponent): bool => $component->name === $setComponent->name);
    }
}
