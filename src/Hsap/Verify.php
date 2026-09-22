<?php

declare(strict_types=1);

namespace Filefabrik\Hsap;

use Filefabrik\Hsap\Exceptions\AlreadySetException;
use Filefabrik\Hsap\Exceptions\ContentDigestException;
use Filefabrik\Hsap\Exceptions\InvalidDerivedKeyException;
use Filefabrik\Hsap\Exceptions\InvalidHeaderKeyException;
use Filefabrik\Hsap\Exceptions\VerifyHeaderSignatureException;
use Filefabrik\Hsap\Verify\HeaderSignature;
use Psr\Http\Message\MessageInterface;

final class Verify
{
    public private(set) ?HeaderSignature $headerSignature = null {
        get => $this->headerSignature ??= $this->getHeaderSignatureInstance();
    }

    public function __construct(
        public private(set) readonly MessageInterface $message,
        public private(set) readonly string $publicPem,
    ) {
    }

    /**
     * @throws InvalidHeaderKeyException
     * @throws AlreadySetException
     * @throws InvalidDerivedKeyException
     * @throws ContentDigestException
     * @throws VerifyHeaderSignatureException
     */
    public function validate(): int
    {
        return $this->headerSignature->validate();
    }

    /**
     * @throws VerifyHeaderSignatureException
     */
    public function prepared(): void
    {
        $this->headerSignature->prepareVerify();
    }

    private function getHeaderSignatureInstance(): HeaderSignature
    {
        $i            = new HeaderSignature($this->message);
        $i->publicPem = $this->publicPem;

        return $i;
    }
}
