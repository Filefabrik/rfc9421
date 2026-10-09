<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signer\Signatures;

abstract class AbstractLines
{
    /**
     * @var \Filefabrik\Rfc9421\Signer\Signatures\Line[]
     */
    private(set) array $lines = [];

    /**
     * @return array<string,string|int>|array{}
     */
    public function toKeyValueArray(): array
    {
        $array = [];
        foreach ($this->lines as $line) {
            $array[$line->key] = $line->value;
        }

        return $array;
    }

    protected function protectedAddEntry(string $key, string|int $value): static
    {
        return $this->protectedAdd(new Line($key, $value));
    }

    protected function protectedAdd(Line $signatureLine): static
    {
        $this->lines[] = $signatureLine;

        return $this;
    }
}
