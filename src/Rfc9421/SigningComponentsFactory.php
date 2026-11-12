<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */

declare(strict_types=1);

namespace Filefabrik\Rfc9421;

use Filefabrik\Rfc9421\Configuration\SigningHeaderTrait;

/**
 * Helper-Stack which components must be signed
 */
final class SigningComponentsFactory
{
    use SigningHeaderTrait;
}
