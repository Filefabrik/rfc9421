<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Enums\Support;

trait HeaderKeysTrait
{
    /**
     * @return string return the value which is HTTP Header Key and also sigining components fieldName part "content-digest";req
     */
    public function fieldName(): string
    {
        return $this->value;
    }
}
