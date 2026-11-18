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

use Filefabrik\Rfc9421\Dto\ComponentItemDto;
use Filefabrik\Rfc9421\Signature\ComponentName;
use Filefabrik\Rfc9421\StructuredFields\Entry;

/**
 * Signature line, the si
 */
final readonly class Line
{
    /**
     * todo make sure it started with an Quoute otherwise it is buggy !!!
     *
     * @param string $signatureComponent the component name "@method"
     *
     * @param string|int $valueToSign the extracted value representation "@method": POST. The valueToSign is parsed as signable value
     */
    public function __construct(private(set) string $signatureComponent, private(set) string|int $valueToSign)
    {
    }

    public function toLineString(): string
    {
        return $this->signatureComponent.Entry::colonWithSpace.$this->valueToSign;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function getComponentItemDto(): ComponentItemDto
    {
        return ComponentName::fromString($this->signatureComponent);
    }

}
