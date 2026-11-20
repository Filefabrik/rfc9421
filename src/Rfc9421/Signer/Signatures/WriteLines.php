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

use Filefabrik\Rfc9421\Extractors\DecodeSignatureComponentEntry;

/**
 * During the Signer Process
 */
final class WriteLines extends AbstractLines
{
    public function addEntry(string $key, string $value): self
    {
        return $this->protectedAddEntry($key, $value);
    }

    /**
     * Todo format Converters
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     * @example from a full signature params line '"content-digest": sha-256=:qvoLOe+II0NxvDyMNg80z+xDv6aD9IkT9w8a340Q1Oo=:',
     */
    public function fromSignatureLine(string $line): self
    {
        $this->add(DecodeSignatureComponentEntry::toLine($line));

        return $this;
    }

    public function add(Line $signatureLine): self
    {
        return $this->protectedAdd($signatureLine);
    }
}
