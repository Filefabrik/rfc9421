<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421;

use Filefabrik\Rfc9421\Exceptions\AlreadySetException;
use Filefabrik\Rfc9421\Exceptions\ContentDigestException;
use Filefabrik\Rfc9421\Exceptions\InvalidDerivedKeyException;
use Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException;
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
     * Flag has to sign the body and the sig must be in header
     *
     * @todo documentation
     */
    public private(set) bool $mustSignBody = true;

    /**
     * Prefix the signature value
     */
    public private(set) ?string $signatureKeyName = null;

    /**
     * for creating the content digest hash it should sha-256 sha256 sha-512 sha512
     */
    public private(set) ?DigestAlgorithmsEnum $contentDigestHashAlgo = null;

    public function __construct(
        public private(set) readonly Metadata $metadata = new Metadata(),
        public private(set) readonly SignatureParams $signatureParams = new SignatureParams(),
    ) {
    }

    // metadata
    public function withCreated(?int $created): self
    {
        $this->metadata->setCreated($created);

        return $this;
    }

    public function withSignatureKeyName(string $signatureKey): self
    {
        $this->signatureKeyName = $signatureKey;

        return $this;
    }

    public function withExpires(?int $expires): self
    {
        $this->metadata->setExpires($expires);

        return $this;
    }

    public function withNonce(?string $nonce): self
    {
        $this->metadata->setNonce($nonce);

        return $this;
    }

    public function withKeyid(?string $keyid): self
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
    public function withSigningAlgorithm(null|string|SigningEnumInterface $algorithm): self
    {
        $this->metadata->setAlg($algorithm);

        return $this;
    }

    public function withTag(?string $tag): self
    {
        $this->metadata->setTag($tag);

        return $this;
    }

    /**
     * want to sign the body in header signatur
     */
    public function mustSignBody(?bool $mustSign = null): self
    {
        $this->mustSignBody = $mustSign ?? true;

        return $this;
    }

    /**
     * Signing header Parameter
     *
     * @param string[] $headerKeys
     *
     * @throws AlreadySetException
     * @throws InvalidDerivedKeyException
     * @throws InvalidHeaderKeyException
     */
    public function signHeader(...$headerKeys): self
    {
        foreach ($headerKeys as $headerKey) {
            $this->signatureParams->add($headerKey);
        }

        return $this;
    }

    /**
     * Hashing the content-digest|body
     *
     * @throws ContentDigestException
     */
    public function getContentDigestHashAlgo(): DigestAlgorithmsEnum
    {
        return $this->contentDigestHashAlgo ??= DigestAlgorithmsEnum::isOwnTypeOrThrow('sha256');
    }

    /**
     * @throws ContentDigestException
     */
    public function setContentDigestHashAlgo(null|string|DigestAlgorithmsEnum $contentDigestHashAlgo): self
    {
        $this->contentDigestHashAlgo = null === $contentDigestHashAlgo ? null :
            DigestAlgorithmsEnum::isOwnTypeOrThrow($contentDigestHashAlgo);

        return $this;
    }
}
