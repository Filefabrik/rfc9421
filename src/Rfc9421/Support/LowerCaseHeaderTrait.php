<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Support;

use Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException;

trait LowerCaseHeaderTrait
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     */
    public function getTrimmedHeaderValue(string $headerName): string
    {
        $headerName = $this->normalizeName($headerName);
        if (array_key_exists($this->normalizeName($headerName), $this->lowerCasedHeaders)) {
            return trim($this->lowerCasedHeaders[$headerName]);
        }

        throw  InvalidHeaderKeyException::headerNotExists($headerName);
    }

    private function normalizeName(string $name): string
    {
        return strtolower($name);
    }

    public function hasHeader(string $headerName): bool
    {
        return array_key_exists($this->normalizeName($headerName), $this->lowerCasedHeaders);
    }
}
