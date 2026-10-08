<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signer;

use UnexpectedValueException;

final class SignatureLines
{
    /**
     * @var \Filefabrik\Rfc9421\Signer\SignatureLine[]
     */
    private(set) array $lines = [];

    public function addEntry(string $key, string $value): self
    {
        return $this->add(new SignatureLine($key, $value));
    }

    public function add(SignatureLine $signatureLine): self
    {
        $this->lines[] = $signatureLine;

        return $this;
    }

    /**
     * @return list<array{string,string}>
     */
    public function toArray(): array
    {
        $array = [];
        foreach ($this->lines as $line) {
            $array[] = [$line->key, $line->value];
        }

        return $array;
    }

    public function getLineNumber(int $lineNumber): SignatureLine
    {
        return $this->lines[$lineNumber] ?? throw new UnexpectedValueException(
            'line number '.$lineNumber.' does not exist',
        );
    }
}
