<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421;

use Filefabrik\Rfc9421\Cryptography\SignatureRegistry;
use Filefabrik\Rfc9421\Cryptography\SigningEnum;
use Filefabrik\Rfc9421\Exceptions\AlreadySetException;
use Filefabrik\Rfc9421\Exceptions\ContentDigestException;
use Filefabrik\Rfc9421\Exceptions\InvalidDerivedKeyException;
use Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException;
use Filefabrik\Rfc9421\Exceptions\SignatureException;
use Filefabrik\Rfc9421\Signer\SignaturBase;
use Psr\Http\Message\RequestInterface;
use UnexpectedValueException;

/**
 * Base singing method
 */
final class Signer
{
    /**
     * Data for signing the relevant header data
     *
     * @var array<string,string>
     */
    private(set) array $signingParameters = [];

    /**
     * @var string|null null throws an error
     */
    private(set) ?string $signingParametersString = null {
        get => $this->signingParametersString ?? throw new UnexpectedValueException('not set before');
    }

    private null|string $signatureBase = null;

    public function __construct(
        private(set) RequestInterface $message,
        private(set) readonly SigningConfiguration $signingConfiguration,
    ) {
    }

    /**
     * @throws AlreadySetException
     * @throws ContentDigestException
     * @throws InvalidDerivedKeyException
     * @throws InvalidHeaderKeyException
     * @throws SignatureException
     */
    public function sign(): RequestInterface
    {
        // set up the content digest into rendering
        if ($this->signingConfiguration->mustSignBody) {
            $this->signingConfiguration->signatureParams->add('content-digest');
        }

        $this->prepareSigning();

        // last phase, crypt and base64_encode. get back header key value  sig1=:base64_signature:
        $cryptedBaseSigString = $this->crypt($this->signingParametersString);

        // setting the signature header into the request
        $this->updateHeader('signature-input', $this->signatureBase)
             ->updateHeader('signature', $cryptedBaseSigString)
        ;

        return $this->message;
    }

    /**
     * für das verify auch verwenden
     *
     * @throws ContentDigestException|InvalidHeaderKeyException
     */
    public function prepareSigning(): void
    {
        $base = new SignaturBase($this->message, $this->signingConfiguration);

        $this->signingParameters = $base->createSignatureLines();

        // rebind because the header maybe changed in signature base class
        $this->message = $base->message;

        // setting the last header before we are signing the "SignatureSigns"
        $this->signatureBase = $base->makeSignatureInput();

        // now set the signature-params ONLY into the signature-base line.
        $this->signingParameters [] = ['@signature-params', $this->signatureBase];

        // makes the array of signingParameters to a string. The string kann be base64EncodedValue
        // keep the $this->signingParametersString public readable. maybe for testing and other stuff
        $this->signingParametersString = SignaturBase::compileSignatureInputToString($this->signingParameters);
    }

    /**
     * Generate the base signature
     *
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public function crypt($signingParametersString): string
    {
        $signature = SignatureRegistry::signing(
            signingEnumInterface: SigningEnum::isOwnTypeOrThrow($this->signingConfiguration->metadata->alg),
            signingDatas        : $signingParametersString,
            privateKey          : $this->signingConfiguration->privatePem,
        );

        return $this->makeSignatureString(base64_encode($signature));
    }

    /**
     * The base64EncodedValue signature-value
     *
     *
     * @return string $signatureKeyName:$signature:
     *
     */
    public function makeSignatureString(string $signature): string
    {
        return sprintf($this->signingConfiguration->signatureKeyName.':%s:', $signature);
    }

    /**
     * internal helper to rebind the changed request object(messageInterface) to $this->message
       */
    private function updateHeader(string $name, ?string $value): self
    {
        $this->message = $this->message->withHeader($name, $value);

        return $this;
    }
}
