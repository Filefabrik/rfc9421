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

/**
 * in the Signers Configuration allowed
 */
trait GenerateContentDigestTrait
{
    private bool $mustGenerateContentDigest = false;

    /**
     * Explizit Command that the content-digest has to be rendered from the message body and MUST be set into the HTTP Message Header as ["content-digest" => 'sha-256=:tFdB/ENGczHMlZMDb66pXoUi2d0OqH2iBHdnN/WV1mc=:']
     */
    public function mustCreateContentDigest(): self
    {
        $this->mustGenerateContentDigest = true;

        return $this;
    }

    public function isMustGenerateContentDigest(): bool
    {
        return $this->mustGenerateContentDigest;
    }

}
