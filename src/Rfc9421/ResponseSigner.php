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

use Filefabrik\Rfc9421\Signer\TraitSigner;
use Psr\Http\Message\ResponseInterface;

/**
 * Only for signing the Response
 * todo how to handle ;req from the request
 */
final class ResponseSigner
{
    use TraitSigner {
        sign as traitSign;
    }

    private(set) ResponseInterface $message;

    public function __construct(
        ResponseInterface $message,
        private(set) readonly SigningConfiguration $signingConfiguration,
        /*
         * Todo, if the request verifier set, it is also possible to handle some ;req headers
         * Todo check we should use another object to handle the ;req for the signed response from a request or signed request
         *
         *
         * */
        private(set) readonly ?VerifyRequest $verifyRequest = null,
    ) {
        $this->message = $message;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EmptyException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     */
    public function sign(): ResponseInterface
    {
        return $this->traitSign();
    }
}
