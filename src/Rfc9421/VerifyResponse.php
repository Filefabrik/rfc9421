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
use Psr\Http\Message\ResponseInterface;

/**
 * Verifies a prepared signed response
 */
final class VerifyResponse extends AbstractVerifying
{
    public function __construct(private(set) readonly VerifyResponsePrepare $preparedVerify)
    {
    }

    public function getMessage(): ResponseInterface
    {
        return $this->preparedVerify->message;
    }

    /**
     * Getting the Signer instance to rebuild the received message
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    protected function getSigner(): ResponseSigner
    {
        $signer = new ResponseSigner(
            message             : clone $this->preparedVerify->message,
            signingConfiguration: $this->getSigningConfiguration(),
            requestLines        : $this->preparedVerify->getExpectedSigners(),
        );

        $signer->prepareSigning();

        return $signer;
    }

}
