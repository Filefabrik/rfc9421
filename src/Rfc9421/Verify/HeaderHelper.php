<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Verify;

use Psr\Http\Message\MessageInterface;

/**
 * fixed methods to extract header lines from Message-Interface
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
        return $this->message->getHeaderLine('signature');
    }

    /**
     * From MessageInterface header-value "signature-input"
     *  todo, there can be more then one header signature-input's
     */
    public function getSignatureInputLine(): string
    {
        return $this->message->getHeaderLine('signature-input');
    }

    public function getContentDigestLine(): string
    {
        return $this->message->getHeaderLine('content-digest');
    }

}
