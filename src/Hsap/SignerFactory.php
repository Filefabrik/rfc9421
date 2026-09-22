<?php

declare(strict_types=1);

namespace Filefabrik\Hsap;

use Filefabrik\Hsap\Exceptions\AlreadySetException;
use Filefabrik\Hsap\Exceptions\InvalidDerivedKeyException;
use Filefabrik\Hsap\Exceptions\InvalidHeaderKeyException;
use Filefabrik\Hsap\Signer\Metadata;
use Filefabrik\Hsap\Signer\SignatureParams;
use Filefabrik\Hsap\Signer\SignBody;
use Psr\Http\Message\MessageInterface;

final class SignerFactory
{
    public private(set) SignatureParams $signatureParams {
        get => $this->signatureParams ??= new SignatureParams();
    }
    public private(set) Metadata        $metadata {
        get => $this->metadata ??= new Metadata();
    }

    public function __construct(
        /**
         * todo make sure the body was set before.
         */
        public private(set) MessageInterface $message,
    ) {
    }

    /**
     * Tell which Parameters has to be in the signing Process
     *
     * @throws InvalidDerivedKeyException
     * @throws InvalidHeaderKeyException
     * @throws AlreadySetException
     */
    public function addSigningHeaderValue($key): self
    {
        $this->signatureParams->add($key);

        return $this;
    }

    /**
     * Central to process
     * todo check content-digest has to be in signature-params
     *
     * @return MessageInterface
     */
    public function sign(): MessageInterface
    {
        $this->signBody();

        return $this->message;
    }

    /**
     * Signs the Body
     *
     * @return void
     */
    private function signBody(): void
    {
        $headerArray   = new SignBody((string)$this->message->getBody(), 'sha512')->getContentDigestHeaderArray();
        $this->message = $this->message->withHeader(...$headerArray);
    }
}
