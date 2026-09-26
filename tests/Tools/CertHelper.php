<?php

declare(strict_types=1);

namespace Filefabrik\HsapTest\Tools;

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

    private function getFC($path): false|string
    {
        return file_get_contents($path);
    }

    public function getPublicPemContent(): false|string
    {
        $fname = $this->name.'-public.pem';
        $path  = $this->getDir().$fname;

        return $this->getFC($path);
    }
}
