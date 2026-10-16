<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421;

use Filefabrik\Rfc9421\Exceptions\ContentDigestException;
use Filefabrik\Rfc9421\Exceptions\SignatureException;
use Filefabrik\Rfc9421\Interfaces\SigningEnumInterface;
use Filefabrik\Rfc9421\Signature\Component;
use Filefabrik\Rfc9421\Signature\Components;
use Filefabrik\Rfc9421\Signature\DerivedComponentEnum;
use Filefabrik\Rfc9421\Signature\InitialContentsEnum;
use Filefabrik\Rfc9421\Signer\Metadata;
use Filefabrik\Rfc9421\Support\DigestAlgorithmsEnum;
use Filefabrik\Rfc9421\Support\ExtractComponentsMethodTrait;
use Filefabrik\Rfc9421\Support\MetadataTrait;
use Filefabrik\Rfc9421\Support\PrivatePemTrait;
use Filefabrik\Rfc9421\Support\PublicPemTrait;
use Filefabrik\Rfc9421\Support\SigningMethodsTrait;

/**
 * Signer Configuration can be configured at different position on your code, such as in a bootstrap file or something
 * Later the Signer Configuration can be used for the signing process in the SignerFactory
 */
final class SigningConfiguration
{
    use PublicPemTrait;
    use PrivatePemTrait;
    use SigningMethodsTrait;
    use ExtractComponentsMethodTrait;
    use MetadataTrait;

    /**
     * Prefix the signature value
     */
    private(set) ?string $signatureKeyName = null;

    /**
     * @var string[]
     */
    private(set) array|null $withoutExistingHeaders = null;

    /**
     * for creating the content digest hash it should sha-256 sha256 sha-512 sha512
     */
    private(set) ?DigestAlgorithmsEnum $contentDigestAlgo = null;

    public function __construct(
        private(set) readonly Metadata $metadata = new Metadata(),
        private(set) Components $signatureComponents = new Components(),
    ) {
    }

    /**
     * Signing algorithm valid for metadata
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

    public function setComponents(Components $components): self
    {
        $this->signatureComponents = $components;

        return $this;
    }

    /**
     * Signing header Parameter
     *
     * @param array{array{string:null|string|\Filefabrik\Rfc9421\Signature\InitialContentsEnum}}|array{string} $headerKeys
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     */
    public function signHeaders(array $headerKeys): self
    {
        foreach ($headerKeys as $headerKey) {
            [$headerKey, $initialContentsEnum] = is_array($headerKey) ? $headerKey : [$headerKey, null];
            $this->signHeader($headerKey, $initialContentsEnum);
        }

        return $this;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     */
    public function signHeader(
        DerivedComponentEnum|string|Component $headerKey,
        null|string|InitialContentsEnum $initialContentsEnum = null,
    ): self {
        if ($headerKey instanceof Component) {
            $this->signatureComponents->set($headerKey);
        } else {
            $this->signatureComponents->set(new Component($headerKey, $initialContentsEnum));
        }

        return $this;
    }

    /**
     * Signing Params they do not need an existing header.
     *
     * @param string[]|null $withoutExistingHeader
     *
     * @example Header content-length will be injected into the real header after processing the signing process.
     *          So you have to forecast the content-length of the body and set this processed value into the signing process.
     *          The receiver of the signed request or signend response verifies the Header value of the (Guzzle) Generated header
     *
     */
    public function processWithoutExistingHeaders(?array $withoutExistingHeader): self
    {
        $this->withoutExistingHeaders = $withoutExistingHeader;

        return $this;
    }

    public function processWithoutExistingHeader(null|string $withoutExistingHeader): self
    {
        $this->withoutExistingHeaders   ??= [];
        $this->withoutExistingHeaders[] = $withoutExistingHeader;

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
    public function getWithoutExistingHeaders(): array
    {
        return $this->hasWithProcessing() ? $this->withoutExistingHeaders :
            throw new SignatureException('Processing is empty! ');
    }

    public function hasWithProcessing(): bool
    {
        return null !== $this->withoutExistingHeaders && count($this->withoutExistingHeaders) > 0;
    }
}
