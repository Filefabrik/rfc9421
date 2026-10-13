<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signature;

/**
 * List of all Components for a singing process
 */
final class Components
{
    /**
     * @var array{\Filefabrik\Rfc9421\Signature\Component}|array{}
     */
    private  array $components = [];

    /**
     * @return array{\Filefabrik\Rfc9421\Signature\Component}|array{}
     */
    public function getComponents(): array
    {
        return $this->components;
    }

    public function adding(string $keyname, null|string|InitialContentsEnum $initialContentsEnum = null): self
    {
        return $this->add(component: new Component(name: $keyname, initialContentsEnum: $initialContentsEnum));
    }

    public function add(Component $component): self
    {
        $this->components[] = $component;

        return $this;
    }
}
