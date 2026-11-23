<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Verify;

use Filefabrik\Rfc9421\Enums\HeaderKeysEnum;
use Filefabrik\Rfc9421\Enums\InternalHeaderKeyEnums;
use Psr\Http\Message\MessageInterface;

/**
 * fixed methods to extract header lines from Message-Interface
 * todo maybe header helper should decoupled from Message-Interface
 */
final readonly class HeaderHelper
{
    public function __construct(private(set) MessageInterface $message)
    {
    }

    /**
     * From MessageInterface header-value "signature"
     * todo, there can be more then one header signature's
     */
    public function getSignatureLine(): string
    {
        return $this->message->getHeaderLine(InternalHeaderKeyEnums::signature->fieldName());
    }

    public function hasSignatureLine(): bool
    {
        return $this->message->hasHeader(InternalHeaderKeyEnums::signature->fieldName());
    }

    /**
     * From MessageInterface header-value "signature-input"
     *  todo, there can be more then one header signature-input's
     */
    public function getSignatureInputLine(): string
    {
        return $this->message->getHeaderLine(InternalHeaderKeyEnums::signature_input->fieldName());
    }

    public function hasSignatureInputLine(): bool
    {
        return $this->message->hasHeader(InternalHeaderKeyEnums::signature_input->fieldName());
    }

    public function getContentDigestLine(): string
    {
        return $this->message->getHeaderLine(HeaderKeysEnum::content_digest->fieldName());
    }

    public function hasContentDigestLine(): bool
    {
        return $this->message->hasHeader(HeaderKeysEnum::content_digest->fieldName());
    }

}
