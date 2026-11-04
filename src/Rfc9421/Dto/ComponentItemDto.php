<?php
/*
 * Copyright (c) 2026.  rt@filefabrik.com
 * proprietary
 * todo insert correct copyright
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Dto;

use Bakame\Http\StructuredFields\Bytes;
use Bakame\Http\StructuredFields\DisplayString;
use Bakame\Http\StructuredFields\Item;
use Bakame\Http\StructuredFields\Token;
use DateTimeImmutable;
use Exception;
use Filefabrik\Rfc9421\Exceptions\SignatureExtractionException;
use Filefabrik\Rfc9421\Signature\ComponentName;
use UnexpectedValueException;

/**
 * Ensured FieldItem read in during the componentNames method
 */
final readonly class ComponentItemDto
{
    private function __construct(
        private(set) string $fieldName,
        private(set) string $httpValue,
        private(set) Item $item,
    ) {
    }

    /**
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public static function fromComponentName(ComponentName $componentName): self
    {
        try {
            return self::fromFieldItem($componentName->getItem());
            // @codeCoverageIgnoreStart
        } catch (Exception $exception) {
            throw new SignatureExtractionException($exception->getMessage(), $exception->getCode(), $exception);
            // @codeCoverageIgnoreEnd
        }
    }

    public static function fromFieldItem(Item $item): self
    {
        return new self(self::validFieldName($item), $item->toHttpValue(), $item);
    }

    /**
     * From Bakame Structured Field as string
     */
    public static function validFieldName(Item $item): string
    {
        $v = $item->value(validate: fn ($value): bool => is_string($value));

        return self::ensureStringValue($v);
    }

    /**
     * Pressing the $v into a string
     */
    private static function ensureStringValue(
        Bytes|Token|DisplayString|DateTimeImmutable|string|int|float|bool $v,
    ): string {
        return is_string($v) ? $v : throw new UnexpectedValueException(
            sprintf('Expected string, got "%s"', gettype($v)),
        );
    }

    public function getItem(): Item
    {
        return $this->item;
    }

    public function getHttpValue(): string
    {
        return $this->httpValue;
    }
}
