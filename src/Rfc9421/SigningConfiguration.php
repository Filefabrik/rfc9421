<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421;

use Filefabrik\Rfc9421\Configuration\{GenerateContentDigestTrait, MetadataTrait, SigningMethodsTrait};
use Filefabrik\Rfc9421\Configuration\PrivatePemTrait;
use Filefabrik\Rfc9421\Configuration\PublicPemTrait;
use Filefabrik\Rfc9421\Enums\DigestAlgorithmsEnum;
use Filefabrik\Rfc9421\Exceptions\ContentDigestException;
use Filefabrik\Rfc9421\Exceptions\SignatureException;
use Filefabrik\Rfc9421\Interfaces\SigningEnumInterface;
use Filefabrik\Rfc9421\Signature\SigningComponents;
use Filefabrik\Rfc9421\Signer\Metadata;
use Filefabrik\Rfc9421\StructuredFields\MethodsMapper;
use Filefabrik\Rfc9421\StructuredFields\StructuredFields;

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

    use GenerateContentDigestTrait;

    /**
     * Prefix the signature value
     */
    private(set) ?string $signatureKeyName = null;

    /**
     * for creating the content digest hash it should sha-256 sha256 sha-512 sha512
     */
    private(set) ?DigestAlgorithmsEnum $contentDigestAlgo = null;

    public function __construct(
        private readonly Metadata $metadata = new Metadata(),
        private SigningComponents $signingComponents = new SigningComponents(),
        /**
         * Methods for extracting the header and recompile the component
         */
        private ?MethodsMapper $methodsMapper = null,
        // todo with prepared config factory
        private ?StructuredFields $structuredFields = null,
    ) {
    }

    public function getSignatureComponents(): SigningComponents
    {
        return $this->signingComponents;
    }

    public function getStructuredFields(): StructuredFields
    {
        return $this->structuredFields ??= StructuredFields::fromDefaultList();
    }

    public function getMethodsMapper(): MethodsMapper
    {
        return $this->methodsMapper ??= MethodsMapper::getDefaultMapper();
    }

    /**
     * Signing algorithm valid for set into the metadata as "alg"
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
        $this->signingComponents = $components;

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

    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }

}
