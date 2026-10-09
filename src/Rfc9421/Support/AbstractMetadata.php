<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Support;

use BadMethodCallException;
use Filefabrik\Rfc9421\Cryptography\SigningEnumsRegistry;
use Filefabrik\Rfc9421\Exceptions\SignatureException;
use Filefabrik\Rfc9421\Interfaces\SigningEnumInterface;
use UnexpectedValueException;

abstract class AbstractMetadata
{
    /**
     * while to string make key value pairs quoted "
     *
     * @var string[]
     */
    public static array $propertiesToQuote = ['nonce', 'keyid', 'alg', 'tag'];

    /**
     * During decode from string/incoming request and this keys exists in Metadata. cast them to integer
     *
     * @var string[]
     */
    protected static array $decodeToInt = ['created', 'expires'];

    /**
     * UNIX timestamp when the signature was generated (recommended for replay protection)
     * Please use UTC Timezone
     */
    private(set) int|null $created = null;

    /**
     * UNIX timestamp after which the signature should be rejected
     */
    private(set) int|null $expires = null;

    /**
     * A random unique value to prevent replay attacks
     */
    private(set) string|null $nonce = null;

    /**
     * Identifier for the key used (helps the server find the right public key)
     */
    private(set) string|null $keyid = null;

    /**
     * The algorithm used to create the signature (e.g., rsa-pss-sha512)
     *
     * @todo define possible algo's based up the current php installation capabilities
     * @see  /docs/iana-rfc9421/signature-algorithms.csv
     */
    private(set) null|SigningEnumInterface $alg = null;

    /**
     * Application-specific identifier
     */
    private(set) string|null $tag = null;

    public function hasTag(): bool
    {
        return null !== $this->tag;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     */
    public function set(string $key, int|string|null $value): static
    {
        $methodName = 'set'.ucfirst($key);

        match ($methodName) {
            'setCreated' => $this->setCreated($this->setIntOrNull($value)),
            'setExpires' => $this->setExpires($this->setIntOrNull($value)),
            'setNonce'   => $this->setNonce($this->setStringOrNull($value)),
            'setKeyid'   => $this->setKeyid($this->setStringOrNull($value)),
            'setAlg'     => $this->setAlg($this->setStringOrSigningEnum($value)),
            'setTag'     => $this->setTag($this->setStringOrNull($value)),
            default      => throw new BadMethodCallException('Method not exists "'.$methodName.'($value)".'),
        };

        return $this;
    }

    public function setCreated(?int $created): static
    {
        $this->created = $created;

        return $this;
    }

    private function setIntOrNull(mixed $value): ?int
    {
        return (is_int($value) || null === $value) ? $value :
            throw new UnexpectedValueException('$value must be int|null');
    }

    public function setExpires(?int $expires): static
    {
        $this->expires = $expires;

        return $this;
    }

    public function setNonce(?string $nonce): static
    {
        $this->nonce = $nonce;

        return $this;
    }

    private function setStringOrNull(mixed $value): ?string
    {
        return null === $value || is_string($value) ? $value :
            throw new UnexpectedValueException('$value must be string|null');
    }

    public function setKeyid(?string $keyid): static
    {
        $this->keyid = $keyid;

        return $this;
    }

    /**
     * @throws SignatureException
     */
    public function setAlg(string|SigningEnumInterface $alg): static
    {
        $this->alg = SigningEnumsRegistry::findEnumOrThrow($alg);

        return $this;
    }
public function getAlgOrThrow():SigningEnumInterface
{
    return $this->alg?? throw new UnexpectedValueException('$alg must be SigningEnumInterface');
}
    private function setStringOrSigningEnum(mixed $value): string|SigningEnumInterface
    {
        return is_string($value) || $value instanceof SigningEnumInterface ? $value :
            throw new UnexpectedValueException('$value must be string|SigningEnumInterface');
    }

    public function setTag(?string $tag): static
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * @return array{}|array{created?: int, expires?: int, nonce?: string, keyid?: string, alg?: string, tag?: string}
     */
    public function toArray(): array
    {
        $metadata = [];
        if (null !== $this->created) {
            $metadata['created'] = $this->created;
        }

        if (null !== $this->expires) {
            $metadata['expires'] = $this->expires;
        }

        if (null !== $this->nonce) {
            $metadata['nonce'] = $this->nonce;
        }

        if (null !== $this->keyid) {
            $metadata['keyid'] = $this->keyid;
        }

        if ($this->alg instanceof SigningEnumInterface) {
            $metadata['alg'] = $this->alg->getValue();
        }

        if (null !== $this->tag) {
            $metadata['tag'] = $this->tag;
        }

        return $metadata;
    }
}
