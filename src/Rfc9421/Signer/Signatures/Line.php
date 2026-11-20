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

/**
 * Signature line, the si
 */
final readonly class Line
{
    public const string colonWithSpace = ': ';

    /**
     * todo make sure it started with an Quote otherwise it is buggy !!!
     *
     * @param string $signatureComponent the component fieldName "@method"
     *
     * @param string|int $valueToSign the extracted value representation "@method": POST. The valueToSign is parsed as signable value
     */
    public function __construct(private(set) string $signatureComponent, private(set) string|int $valueToSign)
    {
    }

    public function toLineString(): string
    {
        return $this->signatureComponent.self::colonWithSpace.$this->valueToSign;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function getComponentItemDto(): ComponentItemDto
    {
        return ComponentName::fromString($this->signatureComponent);
    }

}
