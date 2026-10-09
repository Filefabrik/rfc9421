<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signer\Signatures;

final class ReadLines extends AbstractLines
{
    public function __construct(array|WriteLines $lines)
    {
        if ($lines instanceof WriteLines) {
            $lines = $lines->toKeyValueArray();
        }
        if (is_array($lines)) {
            foreach ($lines as $key => $line) {
                $this->protectedAddEntry($key, $line);
            }
        }
    }
}
