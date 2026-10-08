<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signer\Signatures;

final readonly class Line
{
    public function __construct(private(set) string $key, private(set) string|int $value)
    {
    }

}
