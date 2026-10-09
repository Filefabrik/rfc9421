<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Support;

use UnexpectedValueException;

/**
 * @codeCoverageIgnore
 */
trait PrivatePemTrait
{
    private(set) ?string $privatePem = null;

    public function setPrivatePem(?string $privatePem): self
    {
        $this->privatePem = $privatePem;

        return $this;
    }

    public function getPrivatePemOrThrow(): string
    {
        if (null === $this->privatePem || '' === $this->privatePem) {
            throw new UnexpectedValueException('PrivatePem is null');
        }

        return $this->privatePem;
    }
}
