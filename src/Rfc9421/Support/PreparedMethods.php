<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Support;

use Closure;
use UnexpectedValueException;

final class PreparedMethods
{
    /**
     * @var array{string,callable}|array{}
     */
    private(set) array $methods = [];

    /**
     * @param list<array{string,Closure}>|array{string|Closure}|array{string:Closure} $methods
     *
     * @return $this
     */
    public function adds(array $methods): self
    {
        foreach ($methods as $nr => $method) {
            if (is_string($nr) && $method instanceof Closure) {
                $this->add($nr, $method);

            } else {
                $key = key($method);
                $this->add($key, $method[$key]);
            }
        }

        return $this;
    }

    public function add(string $key, Closure $closure): self
    {
        $this->methods[$key] = $closure;

        return $this;
    }

    public function getMethod(string $key): Closure
    {
        if ($this->hasMethod($key)) {
            return $this->methods[$key];
        }
        throw new UnexpectedValueException(sprintf('Method "%s" not registered.', $key));
    }

    public function hasMethod(string $key): bool
    {
        return array_key_exists($key, $this->methods);
    }
}
