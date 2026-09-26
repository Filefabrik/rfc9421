<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Interfaces;

interface SigningEnumInterface
{
    public static function isOwnTypeOrThrow(self|string|null $key): self;

    public function getName(): string;

    public function getValue(): string;
}
