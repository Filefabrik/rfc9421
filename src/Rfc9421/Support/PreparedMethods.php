<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Support;

use Closure;
use Filefabrik\Rfc9421\Signature\DerivedComponentEnum;
use Filefabrik\Rfc9421\StructuredFields\Entry;
use UnexpectedValueException;

/**
 * Stack which is configurable for each request/response to process the Derived Components or regular headers they are non-normative
 *
 */
final class PreparedMethods
{
    /**
     * @var array{string:Closure}|array{}
     */
    private(set) array $methods = [];

    /**
     * @param array{string:Closure}|array{} $methods
     */
    public function adds(array $methods): self
    {
        foreach ($methods as $nr => $method) {
            $this->add(key: $nr, closure: $method);
        }

        return $this;
    }

    public function add(string|DerivedComponentEnum $key, Closure $closure): self
    {
        $this->methods[$this->normalizeKey($key)] = $closure;

        return $this;
    }

    private function normalizeKey(string|DerivedComponentEnum $key): string
    {
        return $key instanceof DerivedComponentEnum ? $key->value : $this->lowerKey($key);
    }

    private function lowerKey(string $key): string
    {
        return strtolower($key);
    }

    /**
     * Return Entry ensures the correctly handled Processing
     *
     * @param string|\Filefabrik\Rfc9421\Signature\DerivedComponentEnum $key
     * @param array                                                     $params
     *
     * @return \Filefabrik\Rfc9421\StructuredFields\Entry
     */
    public function callWith(string|DerivedComponentEnum $key, array $params = []): Entry
    {
        $nKey   = $this->normalizeKey($key);
        $result = $this->getMethod($nKey)(...$params);

        if (!$result instanceof Entry) {
            return new Entry($nKey, $result);
        }

        return $result;
    }

    public function getMethod(string|DerivedComponentEnum $key): Closure
    {
        $key = $this->normalizeKey($key);

        return $this->methods[$key] ?? throw new UnexpectedValueException(sprintf('Method "%s" not registered.', $key));
    }

    public function hasMethod(string|DerivedComponentEnum $key): bool
    {
        return array_key_exists($this->normalizeKey($key), $this->methods);
    }
}
