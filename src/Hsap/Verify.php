<?php

declare(strict_types=1);

namespace Filefabrik\Hsap;

use Exception;
use Filefabrik\Hsap\Exceptions\AlreadySetException;
use Filefabrik\Hsap\Exceptions\ContentDigestException;
use Filefabrik\Hsap\Exceptions\InvalidDerivedKeyException;
use Filefabrik\Hsap\Exceptions\InvalidHeaderKeyException;
use Filefabrik\Hsap\Exceptions\VerifyHeaderSignatureException;
use Filefabrik\Hsap\Extractors\ContentDigest;
use Filefabrik\Hsap\Signer\Metadata;
use Filefabrik\Hsap\Signer\SignatureParams;
use Filefabrik\Hsap\Verify\HeaderSignatureInput;
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
    public private(set) null|array                  $validSignedParameters    = null;
    public private(set) ?Extractors\HeaderSignature $headerSignatureExtractor = null {
        get => $this->headerSignatureExtractor ??= new Extractors\HeaderSignature(
            $this->getHeaderSignature(),
        );
    }
    private bool                                    $preparedVerifier         = false;
    private ?HeaderSignatureInput                   $signatureInputInstance   = null;

    public function __construct(
        public private(set) readonly MessageInterface|RequestInterface $message,
        /**
         * On first step "Prepare" the extracted Configuration how to handle the own signing of the received request to check the "signature" and the "body" hash
         *
         * @var SignerConfiguration|null
         */
        public private(set) ?SignerConfiguration $signerConfiguration = null {
            get => $this->signerConfiguration ??= new SignerConfiguration();
        },
    ) {
    }

    /**
     * Main method to validate
     *
     * @return false|int
     * @throws ContentDigestException
     * @throws InvalidHeaderKeyException
     * @throws VerifyHeaderSignatureException
     * @throws AlreadySetException
     * @throws InvalidDerivedKeyException
     */
    public function validate(): false|int
    {
        if (!$this->preparedVerifier) {
            $this->prepareVerify();
        }
        $this->prepareSigningComponents();

        if ($this->signerConfiguration->signatureParams->exists('content-digest') && !$this->validateContentDigest()) {
            throw ContentDigestException::bodyTempered();
        }

        $signer = $this->getPreparedSigner();
        // base_decoded signature to get binary
        $sig         = $this->headerSignatureExtractor->getBinarySignatureValue();
        $signerDatas = [
            'data'       => $signer->signingParametersString,
            'signature'  => $sig,
            'public_key' => $this->signerConfiguration->getPublicPem(),
            'algorithm'  => $this->signerConfiguration->metadata->alg,
        ];

        // todo give back validated data

        // now create the compare hash from publicPem
        $verified = openssl_verify(...$signerDatas);

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
        if (!$this->getHeaderSignature()) {
            throw VerifyHeaderSignatureException::missingSignature();
        }
        if (!$this->getHeaderSignatureInput()) {
            throw VerifyHeaderSignatureException::missingSignatureInput();
        }

        /*
         * todo if signatureKeyName already set do not try to extract
         */
        $this->prepareSignatureKeyName();

        /*
         * now prepare the signer configuration which is need for re-compile the request
         */
        $this->prepareConfiguration();

        $this->preparedVerifier = true;
    }

    /**
     * From MessageInterface header-value "signature"
     * todo, there can be more then one header signature's
     *
     * @return string
     */
    public function getHeaderSignature(): string
    {
        return $this->message->getHeaderLine('signature');
    }

    /**
     * From MessageInterface header-value "signature-input"
     *  todo, there can be more then one header signature-input's
     *
     * @return string
     */
    public function getHeaderSignatureInput(): string
    {
        return $this->message->getHeaderLine('signature-input');
    }

    /**
     * Handling the signature key extraction process
     *
     * @return void
     * @throws VerifyHeaderSignatureException
     */
    private function prepareSignatureKeyName(): void
    {
        if (!$this->signerConfiguration->signatureKeyName) {
            try {
                //* Extracts the "sig1=" from the 'sig1=("@method" "content-digest");created=1789634264;tag="mastodon"';
                // todo works if only one signature-input and only one signature
                // todo check more then one
                $signatureKeyName = $this->headerSignatureExtractor->extractSignatureNameKey();
            } catch (Exception) {
                throw VerifyHeaderSignatureException::keynameNotExtracted();
            }
            /**
             * extracted signature key from the "signature" which is the key for extracting the "components" and the "metadata"
             * todo maybe decide we are using an signature key name if there are more then one signatures
             * */
            $this->signerConfiguration
                ->withSignatureKeyName(signatureKey: $signatureKeyName)
            ;
        }
    }

    /**
     * Early step to prepare the signer configuration.
     * The signer configuration can be modified before validation
     *
     * @throws VerifyHeaderSignatureException
     */
    private function prepareConfiguration(): void
    {
        // extract all what was signed and get it from the header
        Metadata::bindMetadata(
            instance: $this->signerConfiguration->metadata,
            metadata: $this->getSignaturInputInstance()->extractedMetadata->toArray(),
        );
    }

    /**
     * @return HeaderSignatureInput
     * @throws VerifyHeaderSignatureException
     */
    private function getSignaturInputInstance(): HeaderSignatureInput
    {
        $headerSignatureInstance = new HeaderSignatureInput(
            headerSignatureInputValue: $this->getHeaderSignatureInput(),
            signatureNameKey         :  $this->signerConfiguration->signatureKeyName
                ,
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
            $this->signerConfiguration->withHeader($signingComponent);
        }
    }

    /**
     * Re-Hash the Body-Hash from the header
     * 'content-digest'  => 'sha512=:PlrIZYU3M76B30wGsL0h6O79BoxHTdAG+RnMPjOyECTSJCN/KnYdOrSCCWjxV3ckkyvdRmZ52//M3WbehCXcPw==:',
     *
     * @return bool
     * @throws ContentDigestException
     */
    private function validateContentDigest(): bool
    {
        $contentDigest          = $this->message->getHeaderLine('content-digest');
        $contentDigestExtractor = new ContentDigest(
            contentDigest: $contentDigest,
        );

        if (!$contentDigestExtractor->validateExtraction()) {
            throw ContentDigestException::wrongInputFormat();
        }
        $instance = Verify\ContentDigest::fromContentDigestExtractor(
            body                  : (string)$this->message->getBody(),
            contentDigestExtractor: $contentDigestExtractor,
        );

        return $instance->verify();
    }

    /**
     * @throws InvalidDerivedKeyException
     * @throws InvalidHeaderKeyException
     * @throws AlreadySetException
     */
    private function getPreparedSigner(): Signer
    {
        $signer = new Signer(
            message      : clone $this->message,
            configuration: $this->signerConfiguration,
        );

        $signer->prepareSigning();

        return $signer;
    }

}
