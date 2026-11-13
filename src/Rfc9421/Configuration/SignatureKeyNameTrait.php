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

trait SignatureKeyNameTrait
{
    /**
     * Prefix the signature value
     */
    private ?string $signatureKeyName = null;

    public function getSignatureKeyName(): ?string
    {
        return $this->signatureKeyName;
    }

    public function setSignatureKeyName(string $signatureKey): self
    {
        $this->signatureKeyName = $signatureKey;

        return $this;
    }
}
