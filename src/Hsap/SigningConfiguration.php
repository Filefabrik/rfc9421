<?php

declare(strict_types=1);

namespace Filefabrik\Hsap;

use Filefabrik\Hsap\Exceptions\AlreadySetException;
use Filefabrik\Hsap\Exceptions\InvalidDerivedKeyException;
use Filefabrik\Hsap\Exceptions\InvalidHeaderKeyException;
use Filefabrik\Hsap\Signer\Metadata;
use Filefabrik\Hsap\Signer\SignatureParams;
use Filefabrik\Hsap\Support\PrepareComponentMethodsTrait;
use Filefabrik\Hsap\Support\PrivatePemTrait;
use Filefabrik\Hsap\Support\PublicPemTrait;

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
     *
     * @var bool
     */
    public private(set) bool $mustSignBody = true;

    /**
     * Prefix the signature value
     *
     * @var string|null
     */
    public private(set) ?string $signatureKeyName = null;

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

    /**
     * @param string $signatureKey
     *
     * @return $this
     */
    public function withSignatureKeyName(string $signatureKey): self
    {
        $this->signatureKeyName = $signatureKey;

        return $this;
    }

    /**
     * @param int|null $expires
     *
     * @return self
     */
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

    public function withAlgorithm(?string $algorithm): self
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
     * @return self
     * @throws AlreadySetException
     * @throws InvalidDerivedKeyException
     * @throws InvalidHeaderKeyException
     */
    public function withHeader(...$headerKeys): self
    {
        foreach ($headerKeys as $headerKey) {
            $this->signatureParams->add($headerKey);
        }

        return $this;
    }

}
