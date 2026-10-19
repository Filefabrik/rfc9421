<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\StructuredFields;

use Closure;
use Filefabrik\Rfc9421\Signature\InitialContentsEnum;
use Filefabrik\Rfc9421\Support\UnquoteHelper;

final class Entry
{
    public const string colon     = ': ';

    public const string semicolon = ';';

    public const string equals    = '=';

    private(set) bool $reqEnum = false;

    /**
     * @var array{Closure}|null
     */
    private(set) array|null $keyMethods = null;

    public function __construct(
        private(set) readonly string $key,
        /**
         * @var string|int|null int while content length
         */
        private(set) readonly null|string|int $value = null,
        private(set) null|InitialContentsEnum $initialContents = null,
    ) {
    }

    public function setInitialContents(InitialContentsEnum $initialContents): self
    {
        // todo check there was already a flag
        $this->initialContents = $initialContents;

        return $this;
    }

    public function setValue(string $value): self
    {
        $new = new self($this->key, $value, $this->initialContents);
        if ($this->reqEnum) {
            $new->withREQEnum();
        }

        // todo check the value was already set and if it is allowed to re-modularize the value

        return $new;
    }

    /**
     * todo check what happens with req
     */
    public function withREQEnum(): self
    {
        $this->reqEnum = true;

        return $this;
    }

    public function withKeyMethod(Closure $method): self
    {
        $this->keyMethods   ??= [];
        $this->keyMethods[] = $method;

        return $this;
    }

    public function parse(): string
    {
        $parts = [];

        $parts[] = $this->parseKeyMethods();

        if ($this->initialContents instanceof \Filefabrik\Rfc9421\Signature\InitialContentsEnum) {
            $parts[] = self::semicolon;
            $parts[] = $this->initialContents->name;

            if (InitialContentsEnum::sf === $this->initialContents || InitialContentsEnum::bs === $this->initialContents) {
                $parts[] = self::colon;
            } elseif (InitialContentsEnum::key === $this->initialContents) {
                $parts[] = self::equals;
            }
        } else {
            $parts[] = self::colon;
        }

        $parts[] = $this->value;

        return implode('', $parts);
    }

    private function parseKeyMethods(): string
    {
        if (null === $this->keyMethods) {
            return UnquoteHelper::quote($this->key);
        }

        $key = $this->key;
        foreach ($this->keyMethods as $keyMethod) {
            $key = $keyMethod->call($this, $key);
        }

        return $key;
    }
}
