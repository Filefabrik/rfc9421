<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Support;

use UnexpectedValueException;

trait PrivatePemTrait
{
    public private(set) ?string $privatePem = null;

    public function getPrivatePem(): ?string
    {
        return $this->privatePem ?? throw new UnexpectedValueException('PrivatePem is null');
    }

    public function setPrivatePem(?string $privatePem): self
    {
        $this->privatePem = $privatePem;

        return $this;
    }
}
