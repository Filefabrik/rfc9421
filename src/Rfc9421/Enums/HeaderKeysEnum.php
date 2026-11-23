<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Enums;

use Filefabrik\Rfc9421\Enums\Support\HeaderKeysTrait;
use Filefabrik\Rfc9421\Interfaces\HeaderKeysEnumInterface;

/**
 * Static Values to prevent from typo errors
 */
enum HeaderKeysEnum: string implements HeaderKeysEnumInterface
{
    use HeaderKeysTrait;

    /*
     * Content-Digest is for the Body
     */
    case content_digest = 'content-digest';

}
