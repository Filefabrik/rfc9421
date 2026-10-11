<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Support;

use Closure;
use UnexpectedValueException;

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

    public function add(string $key, Closure $closure): self
    {
        $key                 = $this->lowerKey($key);
        $this->methods[$key] = $closure;

        return $this;
    }

    private function lowerKey(string $key): string
    {
        return strtolower($key);
    }

    public function getMethod(string $key): Closure
    {
        $key = $this->lowerKey($key);

        return $this->methods[$key] ?? throw new UnexpectedValueException(sprintf('Method "%s" not registered.', $key));
    }

    public function hasMethod(string $key): bool
    {
        return array_key_exists($this->lowerKey($key), $this->methods);
    }
}
