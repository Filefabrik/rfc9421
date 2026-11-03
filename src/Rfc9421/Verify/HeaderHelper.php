<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Verify;

use Filefabrik\Rfc9421\Enums\HeaderKeysEnum;
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
        return $this->message->getHeaderLine(HeaderKeysEnum::signature->value);
    }

    public function hasSignatureLine(): bool
    {
        return $this->message->hasHeader(HeaderKeysEnum::signature->value);
    }

    /**
     * From MessageInterface header-value "signature-input"
     *  todo, there can be more then one header signature-input's
     */
    public function getSignatureInputLine(): string
    {
        return $this->message->getHeaderLine(HeaderKeysEnum::signature_input->value);
    }

    public function hasSignatureInputLine(): bool
    {
        return $this->message->hasHeader(HeaderKeysEnum::signature_input->value);
    }

    public function getContentDigestLine(): string
    {
        return $this->message->getHeaderLine(HeaderKeysEnum::content_digest->value);
    }

    public function hasContentDigestLine(): bool
    {
        return $this->message->hasHeader(HeaderKeysEnum::content_digest->value);
    }

}
