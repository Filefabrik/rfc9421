<?php
/*
 * Copyright (c) 2026.  rt@filefabrik.com
 * proprietary
 * todo insert correct copyright
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signer\Signatures;

/**
 * During the Signer Process
 */
final class WriteLines extends AbstractLines
{
    public function addEntry(string $key, string $value): self
    {
        return $this->protectedAddEntry($key, $value);
    }

    public function add(Line $signatureLine): self
    {
        return $this->protectedAdd($signatureLine);
    }
}
