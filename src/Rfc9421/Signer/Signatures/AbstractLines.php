<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signer\Signatures;

/**
 * Lines as \Filefabrik\Rfc9421\Signer\Signatures\Line[]. They are the most relevant part of the signing process
 */
abstract class AbstractLines
{
    /**
     * @var \Filefabrik\Rfc9421\Signer\Signatures\Line[]|array{}
     */
    private array $lines = [];

    /**
     * for testing
     *
     * @return array<string,string|int>|array{}
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
     * @return \Filefabrik\Rfc9421\Signer\Signatures\Line[]|array{}
     */
    public function getLines(): array
    {
        return $this->lines;
    }

    /**
     * @return array<string,string>
     */
    public function toLineString(): array
    {
        $parts = [];
        foreach ($this->lines as $line) {
            $parts[$line->signatureComponent] = $line->toLineString();
        }

        return $parts;
    }

    /**
     * @return array<string|int>
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

    protected function protectedAddEntry(string $key, string|int $value): static
    {
        return $this->protectedAdd(signatureLine: new Line(signatureComponent: $key, valueToSign: $value));
    }

    protected function protectedAdd(Line $signatureLine): static
    {
        $this->lines[] = $signatureLine;

        return $this;
    }
}
