<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signer;

final readonly class SignatureLine
{
    public function __construct(private(set) string $key, private(set) string|int $value)
    {
    }

}
