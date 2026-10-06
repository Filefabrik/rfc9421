<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Support;

use UnexpectedValueException;

/**
 * @codeCoverageIgnore
 */
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
