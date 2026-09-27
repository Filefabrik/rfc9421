<?php

declare(strict_types=1);

namespace Filefabrik\Hsap;

use Filefabrik\Hsap\Cryptography\SigningEnum;
use Filefabrik\Hsap\Cryptography\VerifyRegistry;
use Filefabrik\Hsap\Exceptions\AlreadySetException;
use Filefabrik\Hsap\Exceptions\ContentDigestException;
use Filefabrik\Hsap\Exceptions\InvalidDerivedKeyException;
use Filefabrik\Hsap\Exceptions\InvalidHeaderKeyException;
use Filefabrik\Hsap\Exceptions\VerifyHeaderSignatureException;
use Filefabrik\Hsap\Signer\Metadata;
use Filefabrik\Hsap\Signer\SignatureParams;
use Filefabrik\Hsap\Verify\HeaderHelper;
use Filefabrik\Hsap\Verify\HeaderSignatureInput;
use Filefabrik\Hsap\Verify\PrepareSignatureKeyName;
use Filefabrik\Hsap\Verify\VerifyContentDigest;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use UnexpectedValueException;

final class Verify
{
    /**
     * Only if verified successfully
     *
     * @var array|null null on not set
     */
    public private(set) null|array $validSignedParameters = null;
    /**
     * Extracting the signature and split into parts
     *
     * @var Extractors\HeaderSignature|null
     */
    public private(set) ?Extractors\HeaderSignature $headerSignatureExtractor = null {
        get => $this->headerSignatureExtractor ??= new Extractors\HeaderSignature(
            signatureString: $this->headerHelper->getSignatureLine(),
        );
    }
    public private(set) ?HeaderHelper               $headerHelper             = null {
        get => $this->headerHelper ??= new HeaderHelper(message: $this->message);
    }
    private bool                                    $preparedVerifier         = false;
    private ?HeaderSignatureInput                   $signatureInputInstance   = null;

    public function __construct(
        public private(set) readonly MessageInterface|RequestInterface $message,
        /**
         * On first step "Prepare" the extracted Configuration how to handle the own signing of the received request to check the "signature" and the "body" hash
         *
         * @var SigningConfiguration|null
         */
        public private(set) ?SigningConfiguration $signingConfiguration = null {
            get => $this->signingConfiguration ??= new SigningConfiguration();
        },
    ) {
    }

    /**
     * Main method to validate
     *
     * @return bool
     * @throws ContentDigestException
     * @throws InvalidHeaderKeyException
     * @throws VerifyHeaderSignatureException
     * @throws AlreadySetException
     * @throws InvalidDerivedKeyException|Exceptions\SignatureException
     */
    public function verifying(): bool
    {
        if (!$this->preparedVerifier) {
            $this->prepareVerify();
        }

        $this->prepareSigningComponents();

        // Validates the Content digest if needed from signature-line sig1=(content-digest)
        if ($this->signingConfiguration->mustSignBody && !$this->validateContentDigest()) {
            return false;
        }

        $signer = $this->getSigner();

        // base_decoded signature to get binary
        $sig = $this->headerSignatureExtractor->getBinarySignatureValue();

        $verified = VerifyRegistry::verifying(
            signingEnum: SigningEnum::isOwnTypeOrThrow($this->signingConfiguration->metadata->alg),
            data       : $signer->signingParametersString,
            signature  : $sig,
            publicKey  : $this->signingConfiguration->getPublicPem(),
        );

        if ($verified) {
            // getting back validated parameters non-tempered parameters
            $this->validSignedParameters = $signer->signingParameters;
        }

        return $verified;
    }

    /**
     * First prepare some request datas
     *
     * @return void
     * @throws VerifyHeaderSignatureException
     */
    public function prepareVerify(): void
    {
        if ($this->preparedVerifier) {
            throw new UnexpectedValueException('already prepared');
        }
        // first stage we must have both signature and signature-input header
        if (!$this->headerHelper->getSignatureLine()) {
            throw VerifyHeaderSignatureException::missingSignature();
        }
        if (!$this->headerHelper->getSignatureInputLine()) {
            throw VerifyHeaderSignatureException::missingSignatureInput();
        }

        /*
         * Extracts the signatureKeyName if not already set above.
         */
        new PrepareSignatureKeyName(
            signingConfiguration    : $this->signingConfiguration,
            headerSignatureExtractor: $this->headerSignatureExtractor,
        )->prepare();
        /*
         * now prepare the signer configuration which is need for re-compile the request
         */
        /**
         * Early step to prepare the signer configuration.
         * The signer configuration can be modified before validation
         *
         * @throws VerifyHeaderSignatureException
         */
        Metadata::bindMetadata(
            instance: $this->signingConfiguration->metadata,
            metadata: $this->getSignaturInputInstance()->extractedMetadata->toArray(),
        );

        $this->preparedVerifier = true;
    }

    /**
     * @return HeaderSignatureInput
     * @throws VerifyHeaderSignatureException
     */
    private function getSignaturInputInstance(): HeaderSignatureInput
    {
        $headerSignatureInstance = new HeaderSignatureInput(
            headerSignatureInputValue: $this->headerHelper->getSignatureInputLine(),
            signatureNameKey         : $this->signingConfiguration->signatureKeyName,
        );
        // throws error
        $headerSignatureInstance->extract();

        return $this->signatureInputInstance = $headerSignatureInstance;
    }

    /**
     * Extract which "Components" must be used to validate the header data
     *
     * @return void
     * @throws AlreadySetException
     * @throws InvalidDerivedKeyException
     * @throws InvalidHeaderKeyException
     */
    private function prepareSigningComponents(): void
    {
        // alle keys they have into rebuildable signing array

        $signingComponents = SignatureParams::fromArray($this->signatureInputInstance->extractedComponents);
        // todo, has nothing todo with configuration

        // no compile the signature array such as in singer
        foreach ($signingComponents->getSignatureParameters() as $signingComponent) {
            $this->signingConfiguration->withHeader($signingComponent);
        }

        /**
         * ignores the content-digest if not want
         */
        $this->signingConfiguration->mustSignBody($signingComponents->exists('content-digest'));
    }

    /**
     *
     * @return bool
     * @throws ContentDigestException
     */
    private function validateContentDigest(): bool
    {
        $verifyInstance = new VerifyContentDigest(
            contentDigest: $this->headerHelper->getContentDigestLine(),
            body         : (string)$this->message->getBody(),
        );
        if ($verified = $verifyInstance->verify()) {
            // rebind the extracted php hash-algo
            // later in verify process, during rebuild the content-digest the algo is needed
            $this->signingConfiguration->setContentDigestHashAlgo(
                contentDigestHashAlgo: $verifyInstance->contentDigestVerifier->phpAlgorithm,
            );
        }

        // no validation of the body
        return $verified;
    }

    /**
     * @throws InvalidDerivedKeyException
     * @throws InvalidHeaderKeyException
     * @throws AlreadySetException
     * @throws ContentDigestException
     */
    private function getSigner(): Signer
    {
        return Signer::getPreparedSigning(
            message             : clone $this->message,
            signingConfiguration: $this->signingConfiguration,
        );
    }

}
