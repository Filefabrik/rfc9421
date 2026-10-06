<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Tools;

use UnexpectedValueException;

final class CertHelper
{
    public function __construct(public private(set) string $name)
    {
    }

    public function getPrivatPemContent(): false|string
    {
        $fname = $this->name.'-private.pem';
        $path  = $this->getDir().$fname;

        return $this->getFC($path);
    }

    public function getDir(): string
    {
        return dirname(__DIR__, 2).'/tests/Keys/';
    }

    private function getFC($path): string
    {
        return is_string($fc = file_get_contents($path)) ? $fc :
            throw new UnexpectedValueException('pem file not found at '.$path);
    }

    public function getPublicPemContent(): false|string
    {
        $fname = $this->name.'-public.pem';
        $path  = $this->getDir().$fname;

        return $this->getFC($path);
    }
}
