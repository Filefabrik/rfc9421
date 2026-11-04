<?php
/*
 * Copyright (c) 2026.  rt@filefabrik.com
 * proprietary
 * todo insert correct copyright
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Interfaces;

interface SigningEnumInterface
{
    public static function isOwnTypeOrThrow(self|string|null $key): self;

    public function getName(): string;

    public function getValue(): string;
}
