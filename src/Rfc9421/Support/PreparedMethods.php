<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Support;

use Closure;
use Filefabrik\Rfc9421\Signature\DerivedComponentEnum;
use UnexpectedValueException;

/**
 * Todo test with normalized keys
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
            $this->add($nr, $method);
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
