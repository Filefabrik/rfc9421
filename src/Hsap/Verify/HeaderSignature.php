<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Verify;

use Exception;
use Filefabrik\Hsap\Exceptions\AlreadySetException;
use Filefabrik\Hsap\Exceptions\ContentDigestException;
use Filefabrik\Hsap\Exceptions\InvalidDerivedKeyException;
use Filefabrik\Hsap\Exceptions\InvalidHeaderKeyException;
use Filefabrik\Hsap\Exceptions\VerifyHeaderSignatureException;
use Filefabrik\Hsap\Extractors\ContentDigest;
use Filefabrik\Hsap\Signer;
use Filefabrik\Hsap\Signer\Metadata;
use Filefabrik\Hsap\Signer\SignatureParams;
use Filefabrik\Hsap\SignerConfiguration;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use UnexpectedValueException;

/**
 * handles the full extracting/validation of the signature and signature-input
 */
final class HeaderSignature
{
    public ?string                           $publicPem     = null;
    public private(set) ?SignerConfiguration $configuration = null;
    /**
     * Only if verified successfully
     *
     * @var array|null null on not set
     */
    public private(set) null|array                                   $validSignedParameters    = null;
    public private(set) ?\Filefabrik\Hsap\Extractors\HeaderSignature $headerSignatureExtractor = null {
        get => $this->headerSignatureExtractor ??= new \Filefabrik\Hsap\Extractors\HeaderSignature(
            $this->getHeaderSignature(),
        );
    }
    private bool                                                     $preparedVerifier         = false;
    private ?HeaderSignatureInput                                    $signatureInputInstance   = null;

    public function __construct(public private(set) readonly MessageInterface|RequestInterface $message)
    {
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

        $this->configuration->setPublicPem($this->publicPem);
        $signer = new Signer(clone $this->message, $this->configuration);

        $signer->prepareSigning();
        // base_decoded signature to get binary
        $sig         = $this->headerSignatureExtractor->getBinarySignatureValue();
        $signerDatas = [
            'data'       => $signer->signingParametersString,
            'signature'  => $sig,
            'public_key' => $this->configuration->getPublicPem(),
            'algorithm'  => $this->configuration->metadata->alg,
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
        try {
            //* Extracts the "sig1=" from the 'sig1=("@method" "content-digest");created=1789634264;tag="mastodon"';
            $signatureKeyName = $this->headerSignatureExtractor->extractSignatureNameKey();
        } catch (Exception) {
            throw VerifyHeaderSignatureException::keynameNotExtracted();
        }

        $this->prepareConfiguration($signatureKeyName);

        // handling the Metadata Extractor
        // todo make methods to control the code execution from metadata such as isExpired

        // todo get back the configuration objects to modify config outside if need
        $this->preparedVerifier = true;
    }

    /**
     * From MessageInterface header-value "signature"
     *
     * @return string
     */
    public function getHeaderSignature(): string
    {
        return $this->message->getHeaderLine('signature');
    }

    /**
     * From MessageInterface header-value "signature-input"
     *
     * @return string
     */
    public function getHeaderSignatureInput(): string
    {
        return $this->message->getHeaderLine('signature-input');
    }

    /**
     * @throws VerifyHeaderSignatureException
     */
    private function prepareConfiguration(string $signatureKeyName): void
    {
        // extract all what was signed and get it from the header
        $this->signatureInputInstance = $this->getSignaturInputInstance();
        $this->configuration          = new SignerConfiguration(
            metadata: Metadata::fromArray(
                metadata: $this->signatureInputInstance->extractedMetadata->toArray(),
            ),
        );
        // extracted signature key from the "signature"
        $this->configuration->withSignatureKeyName($signatureKeyName);
    }

    /**
     * @return HeaderSignatureInput
     * @throws VerifyHeaderSignatureException
     */
    private function getSignaturInputInstance(): HeaderSignatureInput
    {
        $headerSignatureInstance = new HeaderSignatureInput(
            headerSignatureInputValue: $this->getHeaderSignatureInput(),
            signatureNameKey         : $this->headerSignatureExtractor
                ->extractSignatureNameKey(),
        );
        // throws error
        $headerSignatureInstance->extract();

        return $headerSignatureInstance;
    }

    /**
     * extract which parameters must be used to validate the header data
     *
     * @return void
     * @throws AlreadySetException
     * @throws ContentDigestException
     * @throws InvalidDerivedKeyException
     * @throws InvalidHeaderKeyException
     */
    private function prepareSigningComponents(): void
    {
        // alle keys they have into rebuildable signing array

        $signingComponents = SignatureParams::fromArray($this->signatureInputInstance->extractedComponents);
        // check
        if ($signingComponents->exists('content-digest') && !$this->validateContentDigest()) {
            throw ContentDigestException::bodyTempered();
        }
        // no compile the signature array such as in singer
        foreach ($signingComponents->getSignatureParameters() as $signingComponent) {
            $this->configuration->withHeader($signingComponent);
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
        $instance = \Filefabrik\Hsap\Verify\ContentDigest::fromContentDigestExtractor(
            body                  : (string)$this->message->getBody(),
            contentDigestExtractor: $contentDigestExtractor,
        );

        return $instance->verify();
    }

}
