<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signer\Signatures;

use UnexpectedValueException;

final class Lines
{
    /**
     * @var \Filefabrik\Rfc9421\Signer\Signatures\Line[]
     */
    private(set) array $lines = [];

    public function addEntry(string $key, string $value): self
    {
        return $this->add(new Line($key, $value));
    }

    public function add(Line $signatureLine): self
    {
        $this->lines[] = $signatureLine;

        return $this;
    }

    /**
     * @return list<array{string,string|int}>
     */
    public function toArray(): array
    {
        $array = [];
        foreach ($this->lines as $line) {
            $array[] = [$line->key, $line->value];
        }

        return $array;
    }

    public function getLineNumber(int $lineNumber): Line
    {
        return $this->lines[$lineNumber] ?? throw new UnexpectedValueException(
            'line number '.$lineNumber.' does not exist',
        );
    }
}
