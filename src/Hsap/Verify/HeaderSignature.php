<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Verify;

use Exception;
use Filefabrik\Hsap\Exceptions\ContentDigestException;
use Filefabrik\Hsap\Exceptions\InvalidHeaderKeyException;
use Filefabrik\Hsap\Exceptions\VerifyHeaderSignatureException;
use Filefabrik\Hsap\Extractors\ContentDigest;
use Filefabrik\Hsap\Signer;
use Filefabrik\Hsap\Signer\Metadata;
use Filefabrik\Hsap\Signer\SignatureParams;
use Filefabrik\Hsap\SignerConfiguration;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;

/**
 * handles the full extracting/validation of the signature and signature-input
 */
final class HeaderSignature
{
    public private(set) ?string $signatureKeyName = null;

    public ?string                    $publicPem     = null;
    public private(set) ?SignerConfiguration $configuration = null;

    public function __construct(public private(set) readonly MessageInterface|RequestInterface $message)
    {
    }

    /**
     * @throws VerifyHeaderSignatureException
     * @throws ContentDigestException
     * @throws InvalidHeaderKeyException
     */
    public function validate()
    {
        // first stage we must have both signature and signature-input header
        if (!$this->getHeaderSignature()) {
            throw VerifyHeaderSignatureException::missingSignature();
        }
        if (!$this->getHeaderSignatureInput()) {
            throw VerifyHeaderSignatureException::missingSignatureInput();
        }
        try {
            $signatureKeyName = $this->getSignatureKeyName();
        } catch (Exception) {
            throw VerifyHeaderSignatureException::keynameNotExtracted();
        }

        // todo move parts somewhere
        // extract all what was signed and get it from the header
        $signatureInputInstance = $this->getSignaturInputInstance();
        $this->configuration    = new SignerConfiguration(
            metadata: Metadata::fromArray(
                metadata: $signatureInputInstance->extractedMetadata->toArray(),
            ),
        );
        // extracted signature key from the "signature"
        $this->configuration->withSignatureKeyName($signatureKeyName);

        // handling the Metadata Extractor
        // todo make methods to control the code execution from metadata such as isExpired

        // alle keys they have into rebuildable signing array
        $signingComponents = $this->makeSigningParameters($signatureInputInstance->extractedComponents);

        // todo setting of configuration
        $this->configuration->withSignatureKeyName($signatureKeyName);

        // check
        if ($signingComponents->exists('content-digest') && !$this->validateContentDigest()) {
            throw ContentDigestException::bodyTempered();
        }
        // no compile the signature array such as in singer
        foreach ($signingComponents->getSignatureParameters() as $signingComponent) {
            $this->configuration->withHeader($signingComponent);
        }
        $this->configuration->setPublicPem($this->publicPem);
        $signer = new Signer(clone $this->message, $this->configuration);

        $signer->prepareSigning();

        $sig         = $this->getHeaderSignatureDecoded();
        $signerDatas = [
            'data'       => $signer->signingParametersString,
            'signature'  => $sig,
            'public_key' => $this->configuration->getPublicPem(),
            'algorithm'  => 'sha512',
        ];

        // todo give back validated data

        // now create the compare hash from publicPem
        return openssl_verify(...$signerDatas);
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
     * todo to another class because of separation
     * Extracts the "sig1=" from the 'sig1=("@method" "content-digest");created=1789634264;tag="mastodon"';
     *
     * @return string null on error
     */
    private function getSignatureKeyName(): string
    {
        return $this->signatureKeyName = new \Filefabrik\Hsap\Extractors\HeaderSignature(
            $this->getHeaderSignature(),
        )->extractSignatureNameKey();

        // can be empty. or can be null
        // so the signatur-input is also empty before the ()

    }

    private function getSignaturInputInstance(): HeaderSignatureInput
    {
        $headerSignatureInstance = new HeaderSignatureInput(
            headerSignatureInputValue: $this->getHeaderSignatureInput(),
            signatureNameKey         : $this->getSignatureKeyName(),
        );
        // throws error
        $headerSignatureInstance->extract();

        return $headerSignatureInstance;
    }

    private function makeSigningParameters(array $extractedComponents): SignatureParams
    {
        $signingParams = new SignatureParams();

        // try to rebuild the header parameters

        foreach ($extractedComponents as $param) {
            try {
                $signingParams->add($param);
            } catch (Exception$exception) {
                // stop
            }
        }

        return $signingParams;
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

    private function getHeaderSignatureDecoded(): string
    {
        return $this->signatureKeyName = new \Filefabrik\Hsap\Extractors\HeaderSignature(
            $this->getHeaderSignature(),
        )->getBinarySignatureValue();
    }

}
