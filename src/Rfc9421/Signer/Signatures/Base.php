<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signer\Signatures;

use Filefabrik\Rfc9421\Exceptions\ContentDigestException;
use Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException;
use Filefabrik\Rfc9421\Signature\Component;
use Filefabrik\Rfc9421\Signer\PrepareComponents;
use Filefabrik\Rfc9421\Signer\SigCompilerHelper;
use Filefabrik\Rfc9421\Signer\SignBody;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421\Support\PreparedMethods;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

/**
 * Handling the signature base params
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
        $cArray = [];
        foreach ($signatureLines->lines as $signatureLine) {
            $cArray[] = sprintf('"%s": %s', $signatureLine->key, $signatureLine->value);
        }

        // this is what to sign with private key
        return implode("\n", $cArray);
    }

    /**
     * Compiles all configured signature relevant keys with their values. except @signature-params
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public function createSignatureLines(): WriteLines
    {
        $components = new PrepareComponents($this->message);

        /*
         * Prepare methods they must render your signature-params and singing parameters
         */
        if (($preparedComponentMethods = $this->configuration->preparedMethods) instanceof PreparedMethods) {
            $components->setPreparedMethods($preparedComponentMethods);
        }

        foreach ($this->configuration->signatureComponents->getComponents() as $signatureComponent) {
            $v = $this->createSignatureLine(
                signatureComponent: $signatureComponent,
                components        : $components,
            );
            $this->signatureLines->add(
                signatureLine: $v,
            );
        }

        return $this->signatureLines;
    }

    /**
     * Handle the preparation of a signature-base array/line(key, value)
     *
     * @throws ContentDigestException
     * @throws InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    private function createSignatureLine(Component $signatureComponent, PrepareComponents $components): Line
    {
        if ('content-digest' === $signatureComponent->name) {
            return new Line(...$this->handleSignBody());
        }

        return $this->mustProcessingLine($signatureComponent->name) ?
            new Line($signatureComponent->name, $components->processValue($signatureComponent->name)) :
            $this->checkExistingHeader($signatureComponent->name);
    }

    /**
     * Handle the body signing
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @return array{string,string}
     */
    private function handleSignBody(): array
    {
        if ($this->configuration->hasContentDigestHashAlgo()) {
            // set the body digest into the header
            [$headerKey, $value] = new SignBody(
                body     : (string)$this->message->getBody(),
                algorithm: $this->configuration->getContentDigestHashAlgoOrThrow(),
            )->getContentDigestHeaderArray();

            /*
             * rebind the MessageInterface because of cloned change
             */
            $this->message = $this->message->withHeader($headerKey, $value);

            // later to compile
            return [$headerKey, $value];
        }

        throw ContentDigestException::canNotHashWithoutAlgorithm();
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    private function mustProcessingLine(string $signatureParam): bool
    {
        return str_starts_with($signatureParam, '@') ||
            (
                /*
                 * Content-length works over processing
                 */
                $this->configuration->hasWithProcessing() &&
                (
                    in_array(
                        $signatureParam,
                        $this->configuration->getWithoutExistingHeaders(),
                        true,
                    )
                )
            );
    }

    /**
     * Header Key must exist
     *
     * @throws InvalidHeaderKeyException
     */
    private function checkExistingHeader(string $signatureParam): Line
    {
        if ($this->message->hasHeader($signatureParam)) {
            return new Line($signatureParam, $this->message->getHeaderLine($signatureParam));
        }

        throw InvalidHeaderKeyException::headerNotExists($signatureParam);
    }

    /**
     * Concat the signatureKey with the components part in braces with the Metadata
     * Signature-Input: sig1=("@method" "@authority" "@path" "content-type" "content-length");created=1618884475;keyid="test-key"
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
