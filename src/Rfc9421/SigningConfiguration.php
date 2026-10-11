<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421;

use Filefabrik\Rfc9421\Exceptions\ContentDigestException;
use Filefabrik\Rfc9421\Exceptions\SignatureException;
use Filefabrik\Rfc9421\Interfaces\SigningEnumInterface;
use Filefabrik\Rfc9421\Signer\Metadata;
use Filefabrik\Rfc9421\Signer\SignatureParams;
use Filefabrik\Rfc9421\Support\DigestAlgorithmsEnum;
use Filefabrik\Rfc9421\Support\PrepareComponentMethodsTrait;
use Filefabrik\Rfc9421\Support\PrivatePemTrait;
use Filefabrik\Rfc9421\Support\PublicPemTrait;

/**
 * Signer Configuration can be configured at different position on your code, such as in a bootstrap file or something
 * Later the Signer Configuration can be used for the signing process in the SignerFactory
 */
final class SigningConfiguration
{
    use PublicPemTrait;
    use PrivatePemTrait;

    /**
     * simple helper to prepare some methods before executing
     */
    use PrepareComponentMethodsTrait;

    /**
     * Prefix the signature value
     */
    private(set) ?string $signatureKeyName = null;

    /**
     * @var string[]
     */
    private(set) array|null $withoutExistingHeader = null;
    /**
     * for creating the content digest hash it should sha-256 sha256 sha-512 sha512
     */
    private(set) ?DigestAlgorithmsEnum $contentDigestAlgo = null;

    public function __construct(
        private(set) readonly Metadata $metadata = new Metadata(),
        private(set) readonly SignatureParams $signatureParams = new SignatureParams(),
    ) {
    }

    // metadata
    public function setCreated(?int $created): self
    {
        $this->metadata->setCreated($created);

        return $this;
    }

    public function setSignatureKeyName(string $signatureKey): self
    {
        $this->signatureKeyName = $signatureKey;

        return $this;
    }

    public function setExpires(?int $expires): self
    {
        $this->metadata->setExpires($expires);

        return $this;
    }

    public function withNonce(?string $nonce): self
    {
        $this->metadata->setNonce($nonce);

        return $this;
    }

    public function setKeyid(?string $keyid): self
    {
        $this->metadata->setKeyid($keyid);

        return $this;
    }

    /**
     * Signing algorithm valid for metadata
     *
     *
     * @throws SignatureException
     */
    public function setSigningAlgorithm(string|SigningEnumInterface $algorithm): self
    {
        $this->metadata->setAlg($algorithm);

        return $this;
    }

    public function setTag(?string $tag): self
    {
        $this->metadata->setTag($tag);

        return $this;
    }

    /**
     * Signing header Parameter
     *
     * @param string[] $headerKeys
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidDerivedKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     */
    public function signHeaders(array $headerKeys): self
    {
        foreach ($headerKeys as $headerKey) {
            $this->signHeader($headerKey);
        }

        return $this;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidDerivedKeyException
     */
    public function signHeader(string $headerKey): self
    {
        $this->signatureParams->add(key: $headerKey);

        return $this;
    }

    /**
     * Signing Params they do not need an existing header.
     *
     *
     * @return $this
     * @example Header content-length will be injected into the real header after processing the signing process.
     *          So you have to forecast the content-length of the body and set this processed value into the signing process.
     *          The receiver of the signed request or signend response verifies the Header value of the (Guzzle) Generated header
     *
     */
    public function processWithoutExistingHeader(?array $withoutExistingHeader): self
    {
        $this->withoutExistingHeader = $withoutExistingHeader;

        return $this;
    }

    /**
     * Hashing the content-digest|body with algo
     * if set, the header line "content-digest" will be created and the hash value will be stored into the header
     *
     */
    public function getContentDigestAlgo(): ?DigestAlgorithmsEnum
    {
        return $this->contentDigestAlgo;
    }

    /**
     * @throws ContentDigestException
     */
    public function setContentDigestAlgo(null|string|DigestAlgorithmsEnum $contentDigestAlgo): self
    {
        $this->contentDigestAlgo = null === $contentDigestAlgo ?
            null :
            DigestAlgorithmsEnum::isOwnTypeOrThrow($contentDigestAlgo);

        return $this;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     */
    public function getContentDigestHashAlgoOrThrow(): DigestAlgorithmsEnum
    {
        return $this->contentDigestAlgo ?? throw ContentDigestException::hashAlgoNotSupported('Missing');
    }

    public function hasContentDigestHashAlgo(): bool
    {
        return $this->contentDigestAlgo instanceof DigestAlgorithmsEnum;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @return string[]
     */
    public function getWithoutExistingHeader(): array
    {
        return $this->hasWithProcessing() ? $this->withoutExistingHeader :
            throw new SignatureException('Processing is empty! ');
    }

    public function hasWithProcessing(): bool
    {
        return null !== $this->withoutExistingHeader && count($this->withoutExistingHeader) > 0;
    }
}
