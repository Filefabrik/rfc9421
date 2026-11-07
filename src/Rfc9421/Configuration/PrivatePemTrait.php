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

trait PrivatePemTrait
{
    private ?string $privatePem = null;

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EmptyException
     */
    public function getPrivatePemOrThrow(): string
    {
        if (null === $this->privatePem || '' === $this->privatePem) {
            throw EmptyException::privatePemIsEmpty();
        }

        return $this->privatePem;
    }

    public function getPrivatePem(): ?string
    {
        return $this->privatePem;
    }

    public function setPrivatePem(?string $privatePem): self
    {
        $this->privatePem = $privatePem;

        return $this;
    }

}
