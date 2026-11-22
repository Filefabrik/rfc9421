<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signer;

use Filefabrik\Rfc9421\Cryptography\SignatureRegistry;
use Filefabrik\Rfc9421\Dto\ComponentItemDto;
use Filefabrik\Rfc9421\Enums\DerivedComponentEnum;
use Filefabrik\Rfc9421\Enums\HeaderKeysEnum;
use Filefabrik\Rfc9421\Enums\SigningEnum;
use Filefabrik\Rfc9421\Exceptions\ProcessingException;
use Filefabrik\Rfc9421\Signature\ComponentBuilder;
use Filefabrik\Rfc9421\Signer\Signatures\Line;
use Filefabrik\Rfc9421\Signer\Signatures\LinesCompiler;
use Filefabrik\Rfc9421\Signer\Signatures\WriteLines;
use Filefabrik\Rfc9421\StructuredFields\EntryWithValue;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

trait TraitSigner
{
    /**
     * @var string|null null throws an error
     */
    private(set) ?string $signingParametersString = null;

    private(set) null|string $signatureBase = null;

    /**
     * Data for signing the relevant header data
     */
    private null|WriteLines $signingLines = null;

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ProcessingException
     */
    public function getSignedLines(): WriteLines
    {
        return $this->signingLines ?? throw ProcessingException::signingLines();
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EmptyException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\ProcessingException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     */
    private function sign(): ResponseInterface|RequestInterface
    {
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
     * Important Method! It is used by response/request signer and also for the prepare verifying of response and request
     *
     * Todo, SOME derived components not allowed during request and some not during response
     *
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
    public function prepareSigning(): void
    {
        // signing the content-digest into header

        $this->updateHeadersBeforePrepare();

        $base = new LinesCompiler(
            message      : $this->message,
            configuration: $this->signingConfiguration,
            /*
             * Request Lines can be Null if it is a Request Signer
             * todo make sure that expectedSignedLines can only be set if the MessageInterface is a response Interface!
             */
            readLines    : $this->expectedSignedLines ?? null,
        );

        /*
         * Signing line is an array of Lines with the component representation and the calculated values
         * für den response signer kann ein ;asReq benötigt werden.
         */
        $this->signingLines = $base->createSignatureLines();

        // rebind because the header maybe changed in signature base class
        // $this->message = $base->message;

        // setting the last header before we are signing the "SignatureSigns"
        $this->signatureBase = $base->makeSignatureInput();

        // now set the signature-params ONLY into the signature-base line NOT into the HTTP-Header.
        $signatureParamsEntry = EntryWithValue::fromBuilder(
            builder: ComponentBuilder::from(DerivedComponentEnum::signatureParams),
            value  : $this->signatureBase,
        );

        $this->signingLines->add(
            new Line(
                // important to quote
                signatureComponent: $signatureParamsEntry->getComponent(),
                valueToSign       : $signatureParamsEntry->getValue(),
            ),
        );

        // makes the array of signingLines to a string. The string kann be base64EncodedValue
        // keep the $this->signingParametersString public readable. maybe for testing and other stuff
        $this->signingParametersString = LinesCompiler::compileSignatureInputToString($this->signingLines);
    }

    /**
     * Method for the content-digest which will be pushed into the header if not exists
     * todo move out as extra service
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    private function updateHeadersBeforePrepare(): void
    {
        if ($this->signingConfiguration->isMustGenerateContentDigest()) {
            $siCo = $this->signingConfiguration->getSigningComponents();

            if (!$siCo->findComponentByName(HeaderKeysEnum::content_digest->value) instanceof ComponentItemDto) {
                $siCo->sign(HeaderKeysEnum::content_digest->value);
            }

            $contentDigestHeader = new ContentDigestHeader(
                message             : $this->message,
                signingConfiguration: $this->signingConfiguration,
            );
            $this->message       = $contentDigestHeader->handle();
        }
    }

    /**
     * Generate the base signature
     *
     *
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\EmptyException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function crypt(string $signingParametersString): string
    {
        $signature = SignatureRegistry::signing(
            signingEnumInterface: SigningEnum::isOwnTypeOrThrow($this->signingConfiguration->getMetadata()->alg),
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
        return sprintf($this->signingConfiguration->getSignatureKeyName().':%s:', $signature);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ProcessingException
     */
    public function getSigningParametersStringOrThrow(): string
    {
        return $this->signingParametersString ?? throw ProcessingException::signingParametersString();
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
