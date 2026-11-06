<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Support;

use Closure;
use Filefabrik\Rfc9421\Enums\DerivedComponentEnum;
use Filefabrik\Rfc9421\Exceptions\PrepareMethodsException;
use Filefabrik\Rfc9421\StructuredFields\Entry;
use Throwable;

/**
 * Stack which is configurable for each request/response to process the Derived Components or regular headers they are non-normative
 * todo tell what prepare methods do
 */
final class PreparedMethods
{
    /**
     * @var array<string, Closure>|array{}
     */
    private(set) array $methods = [];

    /**
     * @param array<string, Closure>|array{} $methods
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
     * @param array<int,mixed> $params
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     */
    public function callWith(string|DerivedComponentEnum $key, array $params = []): Entry
    {
        $nKey = $this->normalizeKey($key);
        // todo cover error exception
        $closureMethod = $this->getMethod($nKey);

        try {
            $result = $closureMethod(...$params);
        } catch (Throwable$throwable) {
            // binding throwable
            throw PrepareMethodsException::errorFromCalling($nKey, $throwable);
        }

        if (!$result instanceof Entry) {
            return new Entry($nKey, $result);
        }

        return $result;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     */
    public function getMethod(string|DerivedComponentEnum $key): Closure
    {
        $key = $this->normalizeKey($key);

        return $this->methods[$key] ?? throw PrepareMethodsException::methodNotRegistered($key);
    }

    public function hasMethod(string|DerivedComponentEnum $key): bool
    {
        return array_key_exists($this->normalizeKey($key), $this->methods);
    }
}
