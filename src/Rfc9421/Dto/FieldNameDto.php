<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Dto;

use Bakame\Http\StructuredFields\Bytes;
use Bakame\Http\StructuredFields\DisplayString;
use Bakame\Http\StructuredFields\Item;
use Bakame\Http\StructuredFields\Token;
use DateTimeImmutable;
use UnexpectedValueException;

/**
 * Bakame FieldName validated
 * todo validate
 */
final readonly class FieldNameDto
{
    public function __construct(private(set) string $fieldName)
    {
    }

    /**
     * From Bakame Structured Field as string
     */
    public static function fromItem(Item $item): self
    {
        $v = $item->value(validate: fn ($value): bool => is_string($value));

        return new self(fieldName: self::ensureStringValue($v));
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
}
