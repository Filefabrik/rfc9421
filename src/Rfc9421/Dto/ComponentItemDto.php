<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Dto;

use Bakame\Http\StructuredFields\Bytes;
use Bakame\Http\StructuredFields\DisplayString;
use Bakame\Http\StructuredFields\Item;
use Bakame\Http\StructuredFields\Token;
use DateTimeImmutable;
use Exception;
use Filefabrik\Rfc9421\Enums\InitialContentsEnum;
use Filefabrik\Rfc9421\Exceptions\SignatureExtractionException;
use Filefabrik\Rfc9421\Exceptions\UnexpectedValue;
use Filefabrik\Rfc9421\Signature\ComponentName;

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

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public static function fromFieldItem(Item $item): self
    {
        return new self(self::validFieldName($item), $item->toHttpValue(), $item);
    }

    /**
     * From Bakame Structured Field as string
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public static function validFieldName(Item $item): string
    {
        $v = $item->value(validate: fn ($value): bool => is_string($value));

        return self::ensureStringValue($v);
    }

    /**
     * Pressing the $v into a string
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    private static function ensureStringValue(
        Bytes|Token|DisplayString|DateTimeImmutable|string|int|float|bool $v,
    ): string {
        return is_string($v) ? $v : throw new UnexpectedValue(
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

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function withoutInitialContentsReq(): self
    {
        if ($this->hasInitialContentsReq()) {
            return self::fromFieldItem(
                $this->getItem()
                     ->withoutParameterByKeys(InitialContentsEnum::req->name),
            );
        }
        throw new UnexpectedValue('Can not find initial contents enum "req"');
    }

    public function hasInitialContentsReq(): bool
    {
        return null !== $this->getItem()
                             ->parameterByKey(InitialContentsEnum::req->name)
        ;
    }
}
