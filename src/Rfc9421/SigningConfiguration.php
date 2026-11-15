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

use Filefabrik\Rfc9421\Configuration\{ContentDigestTrait,
    MetadataTrait,
    PrivatePemTrait,
    PublicPemTrait,
    SignatureKeyNameTrait,
    SigningMethodsTrait};
use Filefabrik\Rfc9421\Exceptions\SignatureException;
use Filefabrik\Rfc9421\Interfaces\SigningEnumInterface;
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

    use SignatureKeyNameTrait;
    use ContentDigestTrait;

    public function __construct(
        private SigningComponents $signingComponents = new SigningComponents(),
        private readonly Metadata $metadata = new Metadata(),
        /**
         * Methods for extracting the header and recompile the component
         */
        private ?MethodsMapper $methodsMapper = null,
        // todo with prepared config factory
        private ?StructuredFields $structuredFields = null,
    ) {
    }

    public function getSigningComponents(): SigningComponents
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
     * All things you want to sign
     *
     * @return $this
     */
    public function setSigningComponents(SigningComponents $components): self
    {
        $this->signingComponents = $components;

        return $this;
    }

    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }

}
