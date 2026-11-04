<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * todo insert correct copyright
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\StructuredFields;

use Closure;

final readonly class MethodItem
{
    public function __construct(private(set) string $method, private(set) Closure $closure)
    {
    }
}
