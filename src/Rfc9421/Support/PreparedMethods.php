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
use Filefabrik\Rfc9421\Signature\ComponentBuilder;
use Filefabrik\Rfc9421\StructuredFields\EntryWithValue;
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
        foreach ($methods as $fieldName => $method) {
            $this->add(fieldName: $fieldName, closure: $method);
        }

        return $this;
    }

    public function add(string|DerivedComponentEnum $fieldName, Closure $closure): self
    {
        $this->methods[$this->normalizeFieldName($fieldName)] = $closure;

        return $this;
    }

    // without flags
    private function normalizeFieldName(string|DerivedComponentEnum $key): string
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
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function callWith(string|DerivedComponentEnum $fieldName, array $params = []): EntryWithValue
    {
        $normalizedFieldName = $this->normalizeFieldName($fieldName);
        // todo cover error exception
        $closureMethod = $this->getMethod($normalizedFieldName);

        try {
            $result = $closureMethod(...$params);
        } catch (Throwable$throwable) {
            // binding throwable
            throw PrepareMethodsException::errorFromCalling($normalizedFieldName, $throwable);
        }

        if (!$result instanceof EntryWithValue) {
            return EntryWithValue::fromBuilder(builder: new ComponentBuilder($normalizedFieldName))
                                 ->setValue($result)
            ;
        }

        return $result;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     */
    public function getMethod(string|DerivedComponentEnum $key): Closure
    {
        $key = $this->normalizeFieldName($key);

        return $this->methods[$key] ?? throw PrepareMethodsException::methodNotRegistered($key);
    }

    public function hasMethod(string|DerivedComponentEnum $key): bool
    {
        return array_key_exists($this->normalizeFieldName($key), $this->methods);
    }
}
