<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Support;

use UnexpectedValueException;

trait PublicPemTrait
{
    public private(set) ?string $publicPem = null;

    public function getPublicPem(): string
    {
        return $this->publicPem ?? throw new UnexpectedValueException('PublicPem is null');
    }

    public function setPublicPem(?string $publicPem): self
    {
        $this->publicPem = $publicPem;

        return $this;
    }
}
