<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * todo insert correct copyright
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signer;

use Filefabrik\Rfc9421\Cryptography\SignatureRegistry;
use Filefabrik\Rfc9421\Enums\DerivedComponentEnum;
use Filefabrik\Rfc9421\Enums\DigestAlgorithmsEnum;
use Filefabrik\Rfc9421\Enums\HeaderKeysEnum;
use Filefabrik\Rfc9421\Enums\SigningEnum;
use Filefabrik\Rfc9421\Signer\Signatures\Base;
use Filefabrik\Rfc9421\Signer\Signatures\Line;
use Filefabrik\Rfc9421\Signer\Signatures\WriteLines;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use UnexpectedValueException;

trait TraitSigner
{
    /**
     * Data for signing the relevant header data
     */
    private null|WriteLines $signingLines = null;

    /**
     * @var string|null null throws an error
     */
    private(set) ?string $signingParametersString = null;

    private(set) null|string $signatureBase = null;

    public function getSignedLines(): WriteLines
    {
        return $this->signingLines ??
            throw new UnexpectedValueException(
                'Signing\Lines not set',
            );
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
    private function sign(): ResponseInterface|RequestInterface
    {
        // set up the content digest into rendering if the ContentDigestAlgo exists
        if ($this->signingConfiguration->getContentDigestAlgo() instanceof DigestAlgorithmsEnum) {
            $this->signingConfiguration->signatureComponents->addName(HeaderKeysEnum::content_digest->value);
        }

        $this->prepareSigning();

        // last phase, crypt and base64_encode. get back header key value  sig1=:base64_signature:
        $cryptedBaseSigString = $this->crypt(signingParametersString: $this->getSigningParametersStringOrThrow());

        // setting the signature header into the request
        $this->updateHeader(HeaderKeysEnum::signature_input->value, $this->signatureBase)
             ->updateHeader(HeaderKeysEnum::signature->value, $cryptedBaseSigString)
        ;

        return $this->message;
    }

    /**
     * für das verify auch verwenden
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     */
    public function prepareSigning(): void
    {
        $base = new Base(message: $this->message, configuration: $this->signingConfiguration);

        /*
         * Signing line is an array of Lines with the component representation and the calculated values
         */
        $this->signingLines = $base->createSignatureLines();

        // rebind because the header maybe changed in signature base class
        $this->message = $base->message;

        // setting the last header before we are signing the "SignatureSigns"
        $this->signatureBase = $base->makeSignatureInput();

        // now set the signature-params ONLY into the signature-base line NOT into the HTTP-Header.
        $this->signingLines->add(
            new Line(
                signatureComponent: DerivedComponentEnum::signatureParams->value,
                valueToSign       : $this->signatureBase,
            ),
        );

        // makes the array of signingLines to a string. The string kann be base64EncodedValue
        // keep the $this->signingParametersString public readable. maybe for testing and other stuff
        $this->signingParametersString = Base::compileSignatureInputToString($this->signingLines);
    }

    /**
     * Generate the base signature
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public function crypt(string $signingParametersString): string
    {
        $signature = SignatureRegistry::signing(
            signingEnumInterface: SigningEnum::isOwnTypeOrThrow($this->signingConfiguration->metadata->alg),
            signingDatas        : $signingParametersString,
            privateKey          : $this->signingConfiguration->getPrivatePemOrThrow(),
        );

        return $this->makeSignatureString(base64_encode($signature));
    }

    /**
     * The base64EncodedValue signature-value
     *
     * @return string $signatureKeyName:$signature:
     *
     */
    public function makeSignatureString(string $signature): string
    {
        return sprintf($this->signingConfiguration->signatureKeyName.':%s:', $signature);
    }

    public function getSigningParametersStringOrThrow(): string
    {
        return $this->signingParametersString ?? throw new UnexpectedValueException(
            'signing parameter string was not set before',
        );
    }

    /**
     * internal helper to rebind the changed request object(messageInterface) to $this->message
     */
    private function updateHeader(string $name, ?string $value): self
    {
        $this->message = $this->message->withHeader($name, $value ?? '');

        return $this;
    }
}
