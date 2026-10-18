<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\StructuredFields;

use Closure;
use UnexpectedValueException;

/**
 * Methods for Structured Field types
 */
final class MethodsMapper
{
    /**
     * @var array{string:\Filefabrik\Rfc9421\StructuredFields\MethodItem}|array{}|null
     */
    private(set) ?array $methods = null {
        get => $this->methods ??= [];
    }

    public static function defaultETagMethod(string $fieldValue): string
    {
        $resultParts = [];
        $list        = explode(',', $fieldValue);
        foreach ($list as $item) {
            $trimmed = trim($item);

            $resultParts[] = str_starts_with($trimmed, 'W/') ? substr($trimmed, 2).'; w' : $trimmed;
        }

        return implode(', ', $resultParts);
    }

    /**
     * @param array{string:Closure} $methods
     */
    public function addMany(array $methods): self
    {
        foreach ($methods as $methodName => $method) {
            $this->add($methodName, $method);
        }

        return $this;
    }

    public function add(string $methodName, Closure $method): self
    {
        $m                           = new MethodItem($methodName, $method);
        $existingMethods             = $this->methods;
        $existingMethods[$m->method] = $m;
        $this->methods               = $existingMethods;

        return $this;
    }

    /**
     *
     * @return mixed Mixed because of unknown of the given method/method-return
     */
    public function callMethod(string $methodName, ...$params): mixed
    {
        $callableMethod = $this->methods[$methodName] ?? throw new UnexpectedValueException(
            sprintf('Method "%s" does not exist', $methodName),
        );

        return $callableMethod->closure->call($this, ...$params);
    }
}
