<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421;

use Filefabrik\Rfc9421\Signer\TraitSigner;
use Psr\Http\Message\RequestInterface;

/**
 * Base singing method
 */
final class RequestSigner
{
    use TraitSigner {
        sign as traitSign;
    }

    private(set) RequestInterface $message;

    public function __construct(
        RequestInterface $message,
        private(set) readonly SigningConfiguration $signingConfiguration,
    ) {
        $this->message = $message;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidDerivedKeyException
     */
    public function sign(): RequestInterface
    {
        return $this->traitSign();
    }

}
