<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signer\Signatures;

use Filefabrik\Rfc9421\Exceptions\ContentDigestException;
use Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException;
use Filefabrik\Rfc9421\Signer\PrepareComponents;
use Filefabrik\Rfc9421\Signer\SigCompilerHelper;
use Filefabrik\Rfc9421\Signer\SignBody;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421\Support\PreparedMethods;
use Psr\Http\Message\RequestInterface;
use UnexpectedValueException;

/**
 * Handling the signature base params
 */
final class Base
{
    public function __construct(
        private(set) RequestInterface $message,
        private(set) readonly SigningConfiguration $configuration,
        /**
         * ready prepared lines they will be part of the signature
         */
        private(set) readonly WriteLines $signatureLines = new WriteLines(),
    ) {
    }

    /**
     * Make the signature-relevant params array to a string. The string will be base64EncodedValue later
     *
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
     */
    public function createSignatureLines(): WriteLines
    {
        // todo prepare components with extra methods has to be separated
        $components = new PrepareComponents($this->message);

        if (($preparedComponentMethods = $this->configuration->preparedMethods) instanceof PreparedMethods) {
            $components->setPreparedMethods($preparedComponentMethods);
        }

        foreach ($this->configuration->signatureParams->getSignatureParameters() as $signatureParam) {
            $this->signatureLines->add($this->createSignatureLine($signatureParam, $components));
        }

        // at least
        return $this->signatureLines;
    }

    /**
     * Handle the preparation of a signature-base array/line(key, value)
     *
     * @throws ContentDigestException
     * @throws InvalidHeaderKeyException
     */
    private function createSignatureLine(string $signatureParam, PrepareComponents $components): Line
    {
        if ('content-digest' === $signatureParam) {
            return new Line(...$this->handleSignBody());
        }

        // getting the value from header
        return str_starts_with($signatureParam, '@') ?
            // special processing for preconfigured @ components
            new Line($signatureParam, $components->processValue($signatureParam)) :
            // regular. todo make content-length also with a special "processing" value
            // todo, if an header not exists, then must throw during
            $this->checkExistingHeader($signatureParam);
    }

    /**
     * Handle the body signing
     * Todo create a extra config for the digest-hash
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

        throw new UnexpectedValueException(
            'Expecting content-digest from signingConfiguration header but mustSignBody is false',
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
            signatureParams : $this->configuration->signatureParams,
            signatureKeyName: $this->configuration->signatureKeyName ?? '',
        )->makeSig1fromSignatureParams();

        $metadataString = $this->configuration->metadata->toString();

        return $headerString.';'.$metadataString;
    }
}
