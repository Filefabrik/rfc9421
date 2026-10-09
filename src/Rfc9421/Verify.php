<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421;

use BadMethodCallException;
use Filefabrik\Rfc9421\Cryptography\SigningEnum;
use Filefabrik\Rfc9421\Cryptography\VerifyRegistry;
use Filefabrik\Rfc9421\Exceptions\AlreadySetException;
use Filefabrik\Rfc9421\Exceptions\ContentDigestException;
use Filefabrik\Rfc9421\Exceptions\InvalidDerivedKeyException;
use Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException;
use Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException;
use Filefabrik\Rfc9421\Signer\SignatureParams;
use Filefabrik\Rfc9421\Verify\HeaderHelper;
use Filefabrik\Rfc9421\Verify\HeaderSignatureInput;
use Filefabrik\Rfc9421\Verify\PrepareVerify;
use Filefabrik\Rfc9421\Verify\VerifyContentDigest;
use Psr\Http\Message\RequestInterface;
use UnexpectedValueException;

final class Verify
{
    /**
     * Only if verified successfully
     *
     * @var list<array<string,string>>|null null on not set
     */
    private(set) null|array $validSignedParameters = null;

    /**
     * Extracting the signature and split into parts
     */
    private(set) ?Extractors\HeaderSignature $headerSignatureExtractor = null;

    private(set) ?HeaderHelper $headerHelper = null;

    private bool $preparedVerifier = false;

    private ?HeaderSignatureInput $signatureInputInstance = null;

    public function __construct(
        private(set) readonly RequestInterface $message,
        /**
         * On first step "Prepare" the extracted Configuration how to handle the own signing of the received request to check the "signature" and the "body" hash
         *
         * @var SigningConfiguration
         */
        private(set) SigningConfiguration $signingConfiguration = new SigningConfiguration(),
    ) {
    }

    /**
     * Main method to validate
     *
     * @throws ContentDigestException
     * @throws InvalidHeaderKeyException
     * @throws VerifyHeaderSignatureException
     * @throws AlreadySetException
     * @throws InvalidDerivedKeyException|Exceptions\SignatureException|Exceptions\SignatureExtractionException
     */
    public function verifying(): bool
    {
        if (!$this->preparedVerifier) {
            $this->prepareVerify();
        }

        $this->prepareSigningComponents();

        // Validates the Content digest if needed from signature-line sig1=(content-digest)
        if ($this->signingConfiguration->hasContentDigestHashAlgo() && !$this->validateContentDigest()) {
            return false;
        }

        $signer = $this->getSigner();

        // base_decoded signature to get binary
        $sig = $this->headerSignatureExtractor
            ->getBinarySignatureValue()
        ;

        $verified = VerifyRegistry::verifying(
            signingEnum: SigningEnum::isOwnTypeOrThrow($this->signingConfiguration->metadata->alg),
            data       : $signer->getSigningParametersStringOrThrow(),
            signature  : $sig,
            publicKey  : $this->signingConfiguration->getPublicPem(),
        );

        if ($verified) {
            // getting back validated parameters non-tempered parameters
            $this->validSignedParameters = $signer->signingLines->toArray() ?? throw new UnexpectedValueException(
                'Signed Parameters not set',
            );
        }

        return $verified;
    }

    /**
     * First prepare some request datas
     *
     * @throws VerifyHeaderSignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     */
    public function prepareVerify(): void
    {
        if ($this->preparedVerifier) {
            throw new BadMethodCallException('already prepared');
        }

        $prepareVerifyInstance = new PrepareVerify(
            message             : $this->message,
            signingConfiguration: $this->signingConfiguration,
            headerHelper        : $this->getHeaderHelper(),
        );

        // first stage we must have both signature and signature-input header
        $prepareVerifyInstance->prepareVerify();

// rebind
        $this->signingConfiguration     = $prepareVerifyInstance->signingConfiguration;
        $this->headerSignatureExtractor = $prepareVerifyInstance->getHeaderSignatureExtractor();
        $this->signatureInputInstance   = $prepareVerifyInstance->signatureInputInstance;
        $this->preparedVerifier         = true;
    }

    public function getHeaderHelper(): HeaderHelper
    {
        return $this->headerHelper ??= new HeaderHelper(message: $this->message);
    }

    /**
     * Extract which "Components" must be used to validate the header data
     *
     * @throws AlreadySetException
     * @throws InvalidDerivedKeyException
     * @throws InvalidHeaderKeyException
     */
    private function prepareSigningComponents(): void
    {
        // all keys they have into rebuildable signing array

        $signingComponents = SignatureParams::fromArray($this->signatureInputInstance->extractedComponents);

        // no compile the signature array such as in singer
        foreach ($signingComponents->getSignatureParameters() as $signingComponent) {
            $this->signingConfiguration->signHeader($signingComponent);
        }
    }

    /**
     *
     * @throws ContentDigestException
     */
    private function validateContentDigest(): bool
    {
        $verifyInstance = new VerifyContentDigest(
            contentDigest: $this->getHeaderHelper()
                                ->getContentDigestLine(),
            body         : (string)$this->message->getBody(),
        );
        if ($verified = $verifyInstance->verify()) {
            // rebind the extracted php hash-algo
            // later in verify process, during rebuild the content-digest the algo is needed
            $this->signingConfiguration->setContentDigestHashAlgo(
                contentDigestHashAlgo: $verifyInstance->getContentDigestVerifier()->phpAlgorithm,
            );
        }

        // no validation of the body
        return $verified;
    }

    /**
     * Getting the Signer instance to rebuild the received message
     *
     * @throws InvalidDerivedKeyException
     * @throws InvalidHeaderKeyException
     * @throws AlreadySetException
     * @throws ContentDigestException
     */
    private function getSigner(): Signer
    {
        $signer = new Signer(
            message             : clone $this->message,
            signingConfiguration: $this->signingConfiguration,
        );

        $signer->prepareSigning();

        return $signer;
    }

}
