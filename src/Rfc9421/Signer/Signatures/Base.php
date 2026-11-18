<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signer\Signatures;

use Filefabrik\Rfc9421\Dto\ComponentItemDto;
use Filefabrik\Rfc9421\Exceptions\UnexpectedValue;
use Filefabrik\Rfc9421\Signer\PrepareComponents;
use Filefabrik\Rfc9421\Signer\SigCompilerHelper;
use Filefabrik\Rfc9421\SigningConfiguration;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Handling the signature base params
 * The given Signing Components via setHeader('@method') will be prepared into "@method: POST" Lines.
 * This Lines will be used as signing-parameters
 */
final readonly class Base
{
    public function __construct(
        private(set) RequestInterface|ResponseInterface $message,
        private(set) SigningConfiguration $configuration,
        /**
         * ready prepared lines they will be part of the signature
         */
        private(set) WriteLines $signatureLines = new WriteLines(),
        /**
         * ReadLines are the qualified object which comes from a Signed Request
         *
         */
        private(set) null|ReadLines $readLines = null,
    ) {
    }

    /**
     * Make the signature-relevant params array to a string. The string will be base64EncodedValue later
     */
    public static function compileSignatureInputToString(WriteLines $signatureLines): string
    {
        // this is what to sign with private key
        return implode("\n", $signatureLines->toLineString());
    }

    /**
     * Compiles all configured signature relevant keys with their values. except @signature-params
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function createSignatureLines(): WriteLines
    {
        /**
         * Todo validate ;asReq und andere Parameter/Components ob diese für das request oder response signing erlaubt sind.
         * Wenn es ein Response signing mit einem ;asReq ist, muss der Request-Header als Datasource verwendet werden.
         * Für das Validieren einer Signed Response beim Original-Requester muss ebenso auf die validierung der richtigen Source geachtet werden
         */
        $components = new PrepareComponents(message: $this->message, signingConfiguration: $this->configuration);
        /*
         * Prepare methods they must render your signature-params and singing parameters
         * Prepare-Methods können sich unterscheiden ob es ein einfaches request-signing ist oder ob eine response signed wird bzw. eine signierte response signed wird
         */
        $components->setPreparedMethods($this->configuration->getSigningMethodsWithDefaults());

        foreach (
            $this->configuration->getSigningComponents()
                                ->getComponents() as $signatureComponent
        ) {
            if ($signatureComponent->hasInitialContentsReq()) {
                // ;asReq flag so take the original from the asReq

                $line = $this->handleReq($signatureComponent);
            } else {
                $line = new PrepareLine(
                    signingConfiguration: $this->configuration,
                    componentItemDto    : $signatureComponent,
                    message             : $this->message,
                )->handle();
            }

            $this->signatureLines->add(signatureLine: $line);
        }

        return $this->signatureLines;
    }

    /**
     * Todo ugly processing but works
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    private function handleReq(ComponentItemDto $signatureComponent): Line
    {
        if ($this->message instanceof RequestInterface) {
            throw new UnexpectedValue('Request Interface can not have an ;req flag.');
        }

        if (!$this->readLines instanceof ReadLines) {
            throw new UnexpectedValue('For Response signing the signed Request must be set into the Signer!');
        }

        return $this->findAndFix(
            requestLines    : $this->readLines,
            searchInRequest : $signatureComponent->httpValueWithoutReq(),
            requestLineValue: $signatureComponent->getHttpValue(),
        );
    }

    /**
     * Already ensured that the wanted line contains the ";asReq" Flag
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    private function findAndFix(ReadLines $requestLines, string $searchInRequest, string $requestLineValue): Line
    {
        foreach ($requestLines->getLines() as $line) {
            // 1:1 value
            if ($searchInRequest === $line->signatureComponent) {
                // extract value to sign

                return new Line($requestLineValue, $line->valueToSign);
            }
        }

        throw new UnexpectedValue(
            sprintf('Can not find %s in the signed request %s.', $requestLineValue, $searchInRequest),
        );
    }

    /**
     * Concat the signatureKey with the components part in braces and with the Metadata
     * Signature-Input: sig1=("@method" "@authority" "@path" "content-type" "content-length");created=1618884475;keyid="test-key"
     *
     */
    public function makeSignatureInput(): string
    {
        $headerString = new SigCompilerHelper(
            signatureComponents: $this->configuration->getSigningComponents(),
            signatureKeyName   : $this->configuration->getSignatureKeyName() ?? '',
        )->makeSig1fromSignatureParams();

        $metadataString = $this->configuration->getMetadata()
                                              ->toString()
        ;

        return $headerString.';'.$metadataString;
    }

}
