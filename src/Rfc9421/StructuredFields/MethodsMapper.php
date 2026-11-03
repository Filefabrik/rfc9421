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

/**
 * Methods for Structured Field types
 */
final class MethodsMapper
{
    /**
     * @var array<string,\Filefabrik\Rfc9421\StructuredFields\MethodItem>|array{}
     */
    private(set) array $methods = [];

    public static function getDefaultMapper(): self
    {
        return new self()->addMany(self::getDefaults());
    }

    /**
     * @param array<string,Closure>|array{list: Closure, innerlist: Closure, parameters: Closure, dictionary: Closure, item: Closure, etag: Closure, url: Closure, date: Closure} $methods
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
            'etag'       => fn (string $fieldValue): string => StructuredFieldEtag::handle($fieldValue),
            'url'        => fn (string $fieldValue): string => '"'.$fieldValue.'"',
            'date'       => fn (string $fieldValue): string => '@'.strtotime($fieldValue),
        ];
    }

    /**
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @return OuterList::class|InnerList::class| Parameters::class|Dictionary::class|Item::class|string|null Mixed because of unknown of the given method/method-return
     */
    public function callMethod(string $methodName, array $params = []): mixed
    {
        $entry   = $this->methods[$methodName] ?? throw MethodException::notFoundInMethodsMapper($methodName);
        $closure = $entry->closure;

        return $closure(...$params);
    }
}
