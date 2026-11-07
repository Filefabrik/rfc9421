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

trait MetadataTrait
{
    // metadata
    public function setCreated(?int $created): self
    {
        $this->metadata->setCreated($created);

        return $this;
    }

    public function setSignatureKeyName(string $signatureKey): self
    {
        $this->signatureKeyName = $signatureKey;

        return $this;
    }

    public function setExpires(?int $expires): self
    {
        $this->metadata->setExpires($expires);

        return $this;
    }

    public function withNonce(?string $nonce): self
    {
        $this->metadata->setNonce($nonce);

        return $this;
    }

    public function setKeyid(?string $keyid): self
    {
        $this->metadata->setKeyid($keyid);

        return $this;
    }

}
