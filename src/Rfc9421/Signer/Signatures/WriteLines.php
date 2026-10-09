<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signer\Signatures;

use UnexpectedValueException;

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
