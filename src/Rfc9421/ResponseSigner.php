<?php

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
    ) {
        $this->message = $message;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     */
    public function sign(): ResponseInterface
    {
        return $this->traitSign();
    }
}
