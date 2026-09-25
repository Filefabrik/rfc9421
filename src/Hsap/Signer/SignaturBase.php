<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Signer;

use Filefabrik\Hsap\SigningConfiguration;
use Psr\Http\Message\RequestInterface;
use UnexpectedValueException;

/**
 * Handling the signature base params
 */
final class SignaturBase
{
    public function __construct(
        public private(set) RequestInterface $message,
        public private(set) readonly SigningConfiguration $configuration,
    ) {
    }

    /**
     * Make the signature-relevant params array to a string. The string will be base64EncodedValue later
     *
     * @param array $signatureLines
     *
     * @return string
     */
    public static function compileSignatureInputToString(array $signatureLines): string
    {
        $cArray = [];
        foreach ($signatureLines as [$key, $value]) {
            $cArray[] = sprintf('"%s": %s', $key, $value);
        }

        // this is what to sign with private key
        return implode("\n", $cArray);
    }

    /**
     * Compiles all configured signature relevant keys with their values. except @signature-params
     *
     * @return array
     */
    public function createSignatureLines(): array
    {
        $signatureLines = [];

        // todo prepare components with extra methods has to be separated
        $components = new PrepareComponents($this->message);
        $components->setMappingValues($this->configuration->prepareComponentsMethods);

        foreach ($this->configuration->signatureParams->getSignatureParameters() as $signatureParam) {
            $signatureLines[] = $this->createSignatureLine($signatureParam, $components);
        }

        // at least
        return $signatureLines;
    }

    /**
     * Handle the preparation of a signature-base array/line(key, value)
     *
     * @param string            $signatureParam
     * @param PrepareComponents $components
     *
     * @return array
     */
    private function createSignatureLine(string $signatureParam, PrepareComponents $components): array
    {
        if ('content-digest' === $signatureParam) {
            return $this->handleSignBody();
        }

        // getting the value from header
        return str_starts_with($signatureParam, '@') ?
            // special processing for preconfigured @ components
            [$signatureParam, $components->processValue($signatureParam)] :
            // regular. todo make content-length also with a special "processing" value
            [$signatureParam, $this->message->getHeaderLine($signatureParam)];
    }

    /**
     * Handle the body signing
     * Todo create a extra config for the digest-hash
     *
     * @return array
     */
    private function handleSignBody(): array
    {
        if ($this->configuration->mustSignBody) {
            // set the body digest into the header
            [$headerKey, $value] = new SignBody(
                body     : (string)$this->message->getBody(),
                algorithm: $this->configuration->getContentDigestHashAlgo(),
            )->getContentDigestHeaderArray();

            /**
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
     * Concat the signatureKey with the components part in braces with the Metadata
     * Signature-Input: sig1=("@method" "@authority" "@path" "content-type" "content-length");created=1618884475;keyid="test-key"
     *
     * @return string
     */
    public function makeSignatureInput(): string
    {
        $headerString = new SigCompilerHelper(
            $this->configuration->signatureParams,
            $this->configuration->signatureKeyName,
        )->makeSig1fromSignatureParams();

        $metadataString = $this->configuration->metadata->toString();

        return $headerString.';'.$metadataString;
    }
}
