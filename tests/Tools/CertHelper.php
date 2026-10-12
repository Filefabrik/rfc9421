<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Tools;

use UnexpectedValueException;

final class CertHelper
{
    public function __construct(public private(set) readonly string $name, private(set) ?string $dir = null)
    {
    }

    public function getPrivatPemContent(): string
    {
        $fname = $this->name.'-private.pem';
        $path  = $this->getDir().$fname;

        return $this->getFC($path);
    }

    public function getDir(): string
    {
        return $this->dir ??= dirname(__DIR__, 2).'/tests/Keys/';
    }

    private function getFC(string $path): string
    {
        return is_string($fc = file_get_contents($path)) ? $fc :
            throw new UnexpectedValueException('pem file not found at '.$path);
    }

    public function getPublicPemContent(): string
    {
        $fname = $this->name.'-public.pem';
        $path  = $this->getDir().$fname;

        return $this->getFC($path);
    }
}
