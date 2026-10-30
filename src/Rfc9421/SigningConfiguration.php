<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421;

use Filefabrik\Rfc9421\Exceptions\ContentDigestException;
use Filefabrik\Rfc9421\Exceptions\SignatureException;
use Filefabrik\Rfc9421\Interfaces\SigningEnumInterface;
use Filefabrik\Rfc9421\Signature\SigningComponents;
use Filefabrik\Rfc9421\Signer\Metadata;
use Filefabrik\Rfc9421\StructuredFields\MethodsMapper;
use Filefabrik\Rfc9421\StructuredFields\StructuredFields;
use Filefabrik\Rfc9421\Support\DigestAlgorithmsEnum;
use Filefabrik\Rfc9421\Support\MetadataTrait;
use Filefabrik\Rfc9421\Support\PrivatePemTrait;
use Filefabrik\Rfc9421\Support\PublicPemTrait;
use Filefabrik\Rfc9421\Support\SigningHeaderTrait;
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
    use MetadataTrait;
    use SigningHeaderTrait;

    /**
     * Prefix the signature value
     */
    private(set) ?string $signatureKeyName = null;

    /**
     * @var array{int:string}|array{}|null
     */
    private(set) array|null $withoutExistingHeaders = null;

    /**
     * for creating the content digest hash it should sha-256 sha256 sha-512 sha512
     */
    private(set) ?DigestAlgorithmsEnum $contentDigestAlgo = null;

    /**
     * @param \Filefabrik\Rfc9421\Signer\Metadata $metadata
     */
    public function __construct(
        private(set) readonly Metadata $metadata = new Metadata(),
        private(set) SigningComponents $signatureComponents = new SigningComponents(),
        /**
         * Methods for extracting the header and recompile the component
         */
        private(set) ?MethodsMapper $methodsMapper = null,
        // todo with prepared config factory
        private ?StructuredFields $structuredFields = null,
    ) {
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     */
    public function getStructuredFields(): StructuredFields
    {
        return $this->structuredFields ??= StructuredFields::fromDefaultList();
    }

    public function getMethodsMapper(): MethodsMapper
    {
        return $this->methodsMapper ??= MethodsMapper::getDefaultMapper();
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

    /**
     *
     * @return $this
     */
    public function setTag(?string $tag): self
    {
        $this->metadata->setTag($tag);

        return $this;
    }

    /**
     *
     * @return $this
     */
    public function setComponents(SigningComponents $components): self
    {
        $this->signatureComponents = $components;

        return $this;
    }

    /**
     * todo maybe bullshit
     */
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

}
