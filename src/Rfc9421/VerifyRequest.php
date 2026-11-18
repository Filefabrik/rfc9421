<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421;

use Filefabrik\Rfc9421\Verify\AbstractVerifying;
use Psr\Http\Message\RequestInterface;

/**
 * Verification Process of incoming Request
 */
final class VerifyRequest extends AbstractVerifying
{
    public function __construct(private(set) readonly VerifyRequestPrepare $preparedVerify)
    {
    }

    public function getMessage(): RequestInterface
    {
        return $this->preparedVerify->message;
    }

    /**
     * Getting the Signer instance to rebuild the received request message
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    protected function getSigner(): RequestSigner
    {
        $signer = new RequestSigner(
            message             : clone $this->preparedVerify->message,
            signingConfiguration: $this->getVerifySigningConfiguration(),
        );

        $signer->prepareSigning();

        return $signer;
    }

}
