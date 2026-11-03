<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signer\Signatures;

/**
 * Lines as \Filefabrik\Rfc9421\Signer\Signatures\Line[]. They are the most relevant part of the signing process
 */
abstract class AbstractLines
{
    /**
     * @var \Filefabrik\Rfc9421\Signer\Signatures\Line[]
     */
    private(set) array $lines = [];

    /**
     * for testing
     *
     * @return array<string,string>|array{}
     */
    public function toKeyValueArray(): array
    {
        $array = [];
        foreach ($this->lines as $line) {
            $array[$line->signatureComponent] = $line->valueToSign;
        }

        return $array;
    }

    /**
     * @return string[]
     */
    public function values(): array
    {
        $array = [];
        foreach ($this->lines as $line) {
            $array[] = $line->valueToSign;
        }

        return $array;
    }

    /**
     * for testing
     *
     * @return string[]
     */
    public function keys(): array
    {
        $array = [];
        foreach ($this->lines as $line) {
            $array[] = $line->signatureComponent;
        }

        return $array;
    }

    protected function protectedAddEntry(string $key, string $value): static
    {
        return $this->protectedAdd(signatureLine: new Line(signatureComponent: $key, valueToSign: $value));
    }

    protected function protectedAdd(Line $signatureLine): static
    {
        $this->lines[] = $signatureLine;

        return $this;
    }
}
