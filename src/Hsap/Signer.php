<?php

declare(strict_types=1);

namespace Filefabrik\Hsap;

use Filefabrik\Hsap\Cryptography\SignatureRegistry;
use Filefabrik\Hsap\Cryptography\SigningEnum;
use Filefabrik\Hsap\Exceptions\AlreadySetException;
use Filefabrik\Hsap\Exceptions\ContentDigestException;
use Filefabrik\Hsap\Exceptions\InvalidDerivedKeyException;
use Filefabrik\Hsap\Exceptions\InvalidHeaderKeyException;
use Filefabrik\Hsap\Exceptions\SignatureException;
use Filefabrik\Hsap\Signer\SignaturBase;
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
     * @var array
     */
    public private(set) array $signingParameters = [];
    /**
     * @var string|null null throws an error
     */
    public private(set) ?string $signingParametersString = null {
        get => $this->signingParametersString ?? throw new UnexpectedValueException('not set before');
    }

    /**
     * @var string|null
     */
    public private(set) ?string $base64_signature = null {
        get => $this->base64_signature ?? throw new UnexpectedValueException('base64_signature not set before');
    }
    private null|string         $signatureBase    = null;

    public function __construct(
        public private(set) RequestInterface $message,
        public private(set) readonly SigningConfiguration $signingConfiguration,
    ) {
    }

    /**
     * Helper for verify
     *
     * @throws ContentDigestException
     */
    public static function getPreparedSigning(
        RequestInterface $message,
        SigningConfiguration $signingConfiguration,
    ): self {
        $signer = new self(
            message             : $message,
            signingConfiguration: $signingConfiguration,
        );

        $signer->prepareSigning();

        return $signer;
    }

    /**
     * für das verify auch verwenden
     *
     * @throws ContentDigestException
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
     * Generate the base signature
     *
     * @param $signingParametersString
     *
     * @return string
     * @throws SignatureException
     */
    public function crypt($signingParametersString): string
    {
        $signature = SignatureRegistry::signing(
            signingEnumInterface: SigningEnum::isOwnTypeOrThrow($this->signingConfiguration->metadata->alg),
            signingDatas        : $signingParametersString,
            privateKey          : $this->signingConfiguration->privatePem,
        );

        $this->base64_signature = base64_encode($signature);

        return $this->makeSignatureString($this->base64_signature);
    }

    /**
     * The base64EncodedValue signature-value
     *
     * @param string $signature
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
     *
     * @param string $name
     * @param        $value
     *
     * @return self
     */
    private function updateHeader(string $name, $value): self
    {
        $this->message = $this->message->withHeader($name, $value);

        return $this;
    }
}
