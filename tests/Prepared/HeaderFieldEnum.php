<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Prepared;

use Filefabrik\Rfc9421\Enums\Support\HeaderKeysTrait;
use Filefabrik\Rfc9421\Interfaces\HeaderKeysEnumInterface;

enum HeaderFieldEnum: string implements HeaderKeysEnumInterface
{
    use HeaderKeysTrait;

    case content_length = 'content-length';
}
