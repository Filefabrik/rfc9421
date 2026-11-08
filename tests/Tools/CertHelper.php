<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Tools;

use Filefabrik\Rfc9421\Exceptions\UnexpectedValue;

final class CertHelper
{
    public function __construct(private(set) readonly string $name, private(set) ?string $dir = null)
    {
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
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

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    private function getFC(string $path): string
    {
        return is_string($fc = file_get_contents($path)) ? $fc :
            throw new UnexpectedValue('pem file not found at '.$path);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function getPublicPemContent(): string
    {
        $fname = $this->name.'-public.pem';
        $path  = $this->getDir().$fname;

        return $this->getFC($path);
    }
}
