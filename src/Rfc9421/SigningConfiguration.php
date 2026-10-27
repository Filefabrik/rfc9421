<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421;

use Filefabrik\Rfc9421\Exceptions\ContentDigestException;
use Filefabrik\Rfc9421\Exceptions\SignatureException;
use Filefabrik\Rfc9421\Interfaces\SigningEnumInterface;
use Filefabrik\Rfc9421\Signature\ComponentName;
use Filefabrik\Rfc9421\Signature\ComponentNames;
use Filefabrik\Rfc9421\Signature\DerivedComponentEnum;
use Filefabrik\Rfc9421\Signer\Metadata;
use Filefabrik\Rfc9421\StructuredFields\MethodsMapper;
use Filefabrik\Rfc9421\StructuredFields\StructuredFields;
use Filefabrik\Rfc9421\Support\DigestAlgorithmsEnum;
use Filefabrik\Rfc9421\Support\MetadataTrait;
use Filefabrik\Rfc9421\Support\PrivatePemTrait;
use Filefabrik\Rfc9421\Support\PublicPemTrait;
use Filefabrik\Rfc9421\Support\SigningMethodsTrait;
use JsonException;

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

    public function __construct(
        private(set) readonly Metadata $metadata = new Metadata(),
        private(set) ComponentNames $signatureComponents = new ComponentNames(),
        /**
         * Methods for extracting the header and recompile the component
         */
        private(set) ?MethodsMapper $methodsMapper = null {
            get => $this->methodsMapper ??= MethodsMapper::getDefaultMapper();
        },
        // todo with prepared config factory
        private(set) ?StructuredFields $structuredFields = null {
            /**
             * @throws JsonException
             */ get => $this->structuredFields ??= StructuredFields::fromDefaultList();
        },
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

    public function setComponents(ComponentNames $components): self
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
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function signHeaders(array $headerKeys): self
    {
        foreach ($headerKeys as $headerKey) {
            [$headerKey] = is_array($headerKey) ? $headerKey : [$headerKey, null];
            $this->signHeader($headerKey);
        }

        return $this;
    }

    /**
     * Sign Component pure from header
     * todo, if the code /path?param=value&foo=bar&baz=batman&qux= and the signing wich
     * Todo set the sign header has some rules what is valid.
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function signHeader(
        DerivedComponentEnum|string $headerKey,
    ): self {
        // the setter
        $this->signatureComponents->set(ComponentName::fromString($headerKey));

        return $this;
    }

    /**
     * todo maybe bullshit
     *
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
