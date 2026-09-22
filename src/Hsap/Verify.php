<?php

declare(strict_types=1);

namespace Filefabrik\Hsap;

use Filefabrik\Hsap\Verify\HeaderSignature;
use Psr\Http\Message\MessageInterface;

final class Verify
{
    /**
     * @var string|null
     */
    public private(set) ?string $signatureNameKey = null;

    /**
     * @var bool null not checked
     */
    public private(set) bool $validFormats = true;

    public function __construct(
        public private(set) readonly MessageInterface $message,
        public private(set) readonly string $publicPem,
    ) {
    }

    public function validate(): int
    {
        $instance            = new HeaderSignature($this->message);
        $instance->publicPem = $this->publicPem;

        return $instance->validate();
    }
}
