<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Examples\Tools;

use Filefabrik\Rfc9421\Enums\Support\HeaderKeysTrait;
use Filefabrik\Rfc9421\Interfaces\HeaderKeysEnumInterface;

/**
 * example how to use often use header fieldName
 */
enum HeaderFieldEnum: string implements HeaderKeysEnumInterface
{
    use HeaderKeysTrait;

    case content_length = 'content-length';
}
