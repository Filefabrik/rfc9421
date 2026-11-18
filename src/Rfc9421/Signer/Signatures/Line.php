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
     * @param string $signatureComponent the component name "@method"
     * @param string $valueToSign the extracted value representation "@method": POST. The valueToSign is parsed as signable value
     */
    public function __construct(private(set) string $signatureComponent, private(set) string $valueToSign)
    {
    }

    /**
     * Extracting the pure value after the key '"@method": POST' extracts to 'POST'
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function extractValue(): string
    {
        $key = $this->getComponentItemDto()
                    ->getHttpValue()
        ;

        // todo, if no colon space ': ' it is invalid
        return str_replace($key.Entry::colonWithSpace, '', $this->valueToSign);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function getComponentItemDto(): ComponentItemDto
    {
        return ComponentName::fromString($this->signatureComponent);
    }

}
