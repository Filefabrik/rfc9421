<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Support;

use BadMethodCallException;
use Filefabrik\Rfc9421\Cryptography\SigningEnumsRegistry;
use Filefabrik\Rfc9421\Exceptions\SignatureException;
use Filefabrik\Rfc9421\Interfaces\SigningEnumInterface;

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
     * @todo define possible algo's based up the current php installation capabilities
     * @see  /docs/iana-rfc9421/signature-algorithms.csv
     * @var null|SigningEnumInterface
     */
    public private(set) null|SigningEnumInterface $alg = null;

    /**
     * Application-specific identifier
     *
     * @var string|null
     */
    public private(set) string|null $tag = null;

    public function set(string $key, int|string|null $value): static
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
            $metadata['alg'] = $this->alg->getValue();
        }
        if ($this->tag) {
            $metadata['tag'] = $this->tag;
        }

        return $metadata;
    }

    public function setCreated(?int $created): static
    {
        $this->created = $created;

        return $this;
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

    public function setTag(?string $tag): static
    {
        $this->tag = $tag;

        return $this;
    }
}
