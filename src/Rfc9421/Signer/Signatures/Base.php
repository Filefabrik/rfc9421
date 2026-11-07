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

use Filefabrik\Rfc9421\Signer\PrepareComponents;
use Filefabrik\Rfc9421\Signer\SigCompilerHelper;
use Filefabrik\Rfc9421\SigningConfiguration;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

/**
 * Handling the signature base params
 * The given Signing Components via setHeader('@method') will be prepared into "@method: POST" Lines.
 * This Lines will be used as signing-parameters
 */
final class Base
{
    public function __construct(
        private(set) RequestInterface|Response $message,
        private(set) readonly SigningConfiguration $configuration,
        /**
         * ready prepared lines they will be part of the signature
         */
        private(set) readonly WriteLines $signatureLines = new WriteLines(),
    ) {
    }

    /**
     * Make the signature-relevant params array to a string. The string will be base64EncodedValue later
     */
    public static function compileSignatureInputToString(WriteLines $signatureLines): string
    {
        // this is what to sign with private key
        return implode("\n", $signatureLines->values());
    }

    /**
     * Compiles all configured signature relevant keys with their values. except @signature-params
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     */
    public function createSignatureLines(): WriteLines
    {
        $components = new PrepareComponents(message: $this->message, signingConfiguration: $this->configuration);

        /*
         * Prepare methods they must render your signature-params and singing parameters
         */

        $components->setPreparedMethods($this->configuration->getSigningMethodsWithDefaults());

        foreach ($this->configuration->signatureComponents->getComponents() as $signatureComponent) {
            $line = new PrepareLine(
                signingConfiguration: $this->configuration,
                componentItemDto    : $signatureComponent,
                message             : $this->message,
            )->handle();
            $this->signatureLines->add(signatureLine: $line);
        }

        return $this->signatureLines;
    }

    /**
     * Concat the signatureKey with the components part in braces and with the Metadata
     * Signature-Input: sig1=("@method" "@authority" "@path" "content-type" "content-length");created=1618884475;keyid="test-key"
     *
     */
    public function makeSignatureInput(): string
    {
        $headerString = new SigCompilerHelper(
            signatureComponents: $this->configuration->signatureComponents,
            signatureKeyName   : $this->configuration->signatureKeyName ?? '',
        )->makeSig1fromSignatureParams();

        $metadataString = $this->configuration->metadata->toString();

        return $headerString.';'.$metadataString;
    }

}
