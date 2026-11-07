<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Configuration;

use Filefabrik\Rfc9421\Exceptions\EmptyException;

trait PublicPemTrait
{
    private ?string $publicPem = null;

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EmptyException
     */
    public function getPublicPemOrThrow(): string
    {
        if (null === $this->publicPem || '' === $this->publicPem) {
            throw EmptyException::publicPemIsEmpty();
        }

        return $this->publicPem;
    }

    public function getPublicPem(): ?string
    {
        return $this->publicPem;
    }

    public function setPublicPem(?string $publicPem): self
    {
        $this->publicPem = $publicPem;

        return $this;
    }
}
