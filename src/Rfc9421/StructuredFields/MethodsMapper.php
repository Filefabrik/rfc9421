<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\StructuredFields;

use Bakame\Http\StructuredFields\Dictionary;
use Bakame\Http\StructuredFields\InnerList;
use Bakame\Http\StructuredFields\Item;
use Bakame\Http\StructuredFields\OuterList;
use Bakame\Http\StructuredFields\Parameters;
use Closure;
use Filefabrik\Rfc9421\Exceptions\MethodException;
use Filefabrik\Rfc9421\Methods\StructuredFieldEtag;
use ReflectionException;
use ReflectionFunction;
use Throwable;
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

    public static function getDefaultMapper(): self
    {
        return new self()->addMany(self::getDefaults());
    }

    /**
     * @param array{string:Closure}|array{list: Closure, innerlist: Closure, parameters: Closure, dictionary: Closure, item: Closure, etag: Closure, url: Closure, date: Closure} $methods
     */
    public function addMany(array $methods): self
    {
        foreach ($methods as $methodName => $closure) {
            $this->add($methodName, $closure);
        }

        return $this;
    }

    public function add(string $methodName, Closure $method): self
    {
        $mItem                           = new MethodItem($methodName, $method);
        $existingMethods                 = $this->methods;
        $existingMethods[$mItem->method] = $mItem;
        $this->methods                   = $existingMethods;

        return $this;
    }

    /**
     * @return array{list: Closure, innerlist: Closure, parameters: Closure, dictionary: Closure, item: Closure, etag: Closure, url: Closure, date: Closure}
     */
    private static function getDefaults(): array
    {
        return [
            'list'       => fn (string $fieldValue): OuterList => OuterList::fromHttpValue($fieldValue),
            'innerlist'  => fn (string $fieldValue): InnerList => InnerList::fromHttpValue($fieldValue),
            'parameters' => fn (string $fieldValue): Parameters => Parameters::fromHttpValue($fieldValue),
            'dictionary' => fn (string $fieldValue): Dictionary => Dictionary::fromHttpValue($fieldValue),
            'item'       => fn (string $fieldValue): Item => Item::fromHttpValue($fieldValue),
            'etag'       => StructuredFieldEtag::handle(...),
            'url'        => fn (string $fieldValue): string => '"'.$fieldValue.'"',
            'date'       => fn (string $fieldValue): string => '@'.strtotime($fieldValue),
        ];
    }

    /**
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @return mixed Mixed because of unknown of the given method/method-return
     */
    public function callMethod(string $methodName, ...$params): mixed
    {
        $entry = $this->methods[$methodName] ?? throw new UnexpectedValueException(
            sprintf('Method "%s" does not exist', $methodName),
        );

        try {
            $isAnonymous = new ReflectionFunction($entry->closure)->isAnonymous();
        } catch (ReflectionException $reflectionException) {
            throw MethodException::reflectionCallError($reflectionException->getMessage());
        }

        /*
         * fixing static
         */
        if (!$isAnonymous) {
            $c = $entry->closure;
            try {
                return $c(...$params);
            } catch (Throwable$throwable) {
                throw MethodException::closureCallError($throwable->getMessage());
            }
        }

        try {
            return $entry->closure->call($this, ...$params);
        } catch (Throwable $throwable) {
            throw MethodException::closureCallError($throwable->getMessage());
        }
    }
}
