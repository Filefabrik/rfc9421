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

/**
 * only internal usage. Can not be set from outside
 */
enum InternalHeaderKeyEnums: string
{
    use HeaderKeysTrait;

    /**
     * fixed values
     */
    case signature_input = 'signature-input';

    case signature = 'signature';

}
