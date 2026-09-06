<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Signer;

use BadMethodCallException;

/**
 * The Signature-Input header also includes metadata parameters (after the semicolon):
 */
final class Metadata
{
    /**
     * UNIX timestamp when the signature was generated (recommended for replay protection)
     *
     * @var int|null
     */
    public private(set) int|null $created = null;
    /**
     * UNIX timestamp after which the signature should be rejected
     *
     * @var int|null
     */
    public private(set) int|null $expires = null;
    /**
     * A random unique value to prevent replay attacks
     *
     * @var string|null
     */
    public private(set) string|null $nonce = null;

    /**
     * Identifier for the key used (helps the server find the right public key)
     *
     * @var string|null
     */
    public private(set) string|null $keyid = null;
    /**
     * The algorithm used to create the signature (e.g., rsa-pss-sha512)
     *
     * @var string|null
     */
    public private(set) string|null $alg = null;

    /**
     * Application-specific identifier
     *
     * @var string|null
     */
    public private(set) string|null $tag = null;

    /**
     * @param array $metadata
     *
     * @return self
     */
    public static function fromArray(array $metadata): self
    {
        $self = new self();
        foreach ($metadata as $key => $value) {
            $self->set($key, $value);
        }

        return $self;
    }

    public function set(string $key, int|string|null $value): self
    {
        $methodName = 'set'.ucfirst($key);
        method_exists($this, $methodName) ? $this->$methodName($value) :
            throw new BadMethodCallException('Method not exists "'.$methodName.'($value)".');

        return $this;
    }

    /**
     * @return array<string,string|int>
     */
    public function toArray(): array
    {
        $metadata = [];
        if ($this->created) {
            $metadata['created'] = $this->created;
        }
        if ($this->expires) {
            $metadata['expires'] = $this->expires;
        }
        if ($this->nonce) {
            $metadata['nonce'] = $this->nonce;
        }
        if ($this->keyid) {
            $metadata['keyid'] = $this->keyid;
        }
        if ($this->alg) {
            $metadata['alg'] = $this->alg;
        }
        if ($this->tag) {
            $metadata['tag'] = $this->tag;
        }

        return $metadata;
    }

    public function setCreated(?int $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function setExpires(?int $expires): self
    {
        $this->expires = $expires;

        return $this;
    }

    public function setNonce(?string $nonce): self
    {
        $this->nonce = $nonce;

        return $this;
    }

    public function setKeyid(?string $keyid): self
    {
        $this->keyid = $keyid;

        return $this;
    }

    public function setAlg(?string $alg): self
    {
        $this->alg = $alg;

        return $this;
    }

    public function setTag(?string $tag): self
    {
        $this->tag = $tag;

        return $this;
    }
}
