<?php
/*
 * Copyright (c) 2026.  rt@filefabrik.com
 * proprietary
 * todo insert correct copyright
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signature;

use Bakame\Http\StructuredFields\Item;
use Exception;
use Filefabrik\Rfc9421\Dto\ComponentItemDto;
use Filefabrik\Rfc9421\Enums\DerivedComponentEnum;
use Filefabrik\Rfc9421\Enums\InitialContentsEnum;
use Filefabrik\Rfc9421\Exceptions\EnumTypeException;
use Filefabrik\Rfc9421\Exceptions\SignatureExtractionException;
use Filefabrik\Rfc9421\Support\UnquoteHelper;

/**
 * This method is only used to prepare internal setting during the setHeader
 * Single component with InitialContentsEnum or without InitialContentsEnum
 * or with structured fields
 */
final class ComponentName
{
    /**
     * Todo some rules
     * key must have a value
     * name must have a value
     * sf and bs does not have a valid
     */
    private function __construct(
        /**
         * todo is unquoted!!!
         *
         * @var string $name name of the component @authority|content-length|"example-dict";name="baz"
         */
        private(set) string|DerivedComponentEnum $name {
            /**
             * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
             */ set => $value instanceof DerivedComponentEnum ? $value : $this->setValidatedName($value);
        },
        private(set) string|null|InitialContentsEnum $initialContents = null {
            /**
             * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
             */ set => InitialContentsEnum::isOwnTypeOrNullOrThrow($value);
        },
        /**
         * name and Key have a value
         * sf and bs does not have a value
         *
         */
        private(set) readonly ?string $keyOrNameValue = null,
    ) {
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    private function setValidatedName(string $name): string|DerivedComponentEnum
    {
        // todo validate value against unwanted chars/expressions
        $lower = strtolower($name);

        // todo fine-style this setter

        if (str_starts_with($lower, '@')) {
            return DerivedComponentEnum::isOwnValueOrNull($lower) ??
                throw EnumTypeException::TypeError(
                    $lower,
                    DerivedComponentEnum::class,
                );
        }

        return $lower;
    }

    /**
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public static function fromString(string|DerivedComponentEnum $name): ComponentItemDto
    {
        $self = $name instanceof DerivedComponentEnum ? new self($name) : self::extractWithRegex($name);

        return ComponentItemDto::fromComponentName($self);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    private static function extractWithRegex(string $segment): self
    {
        $s       = implode('|', [InitialContentsEnum::key->name, InitialContentsEnum::name->name]);
        $pattern = '^"(.*?)";('.$s.')="(.*?)"$';
        preg_match('#'.$pattern.'#', $segment, $matches);

        if (4 === count($matches)) {
            return self::withKeyInitialContentValue(
                name           : $matches[1],
                initialContents: $matches[2],
                keyOrNameValue : $matches[3],
            );
        }

        $s = implode('|', InitialContentsEnum::names());
        preg_match('#^"(.*?)";('.$s.')$#', $segment, $matches);

        if (3 === count($matches)) {
            return new ComponentName($matches[1], $matches[2]);
        }

        $simple = '#^"(.*?)"$#';
        preg_match($simple, $segment, $matches);

        if (2 === count($matches)) {
            return new ComponentName($matches[1]);
        }

        if (!str_contains($segment, ';')) { // else normal format
            return new ComponentName($segment);
        }

        throw new SignatureExtractionException(sprintf('Segment has unknown format "%s"', $segment));
    }

    /**
     * 3-Parts such as '"example-dict";key="a"'
     */
    public static function withKeyInitialContentValue(
        string|DerivedComponentEnum $name,
        string|InitialContentsEnum $initialContents,
        string|null $keyOrNameValue,
    ): self {
        return new ComponentName(name: $name, initialContents: $initialContents, keyOrNameValue: $keyOrNameValue);
    }

    /**
     * only the Name is need and known
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public static function fromComponentName(string|DerivedComponentEnum $name): ComponentItemDto
    {
        $self = $name instanceof DerivedComponentEnum ? new self($name) : self::extractWithRegex($name);

        return ComponentItemDto::fromComponentName($self);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public static function fromNameAndInitialContent(
        string|DerivedComponentEnum $name,
        InitialContentsEnum|string $initialContentsEnum,
    ): ComponentItemDto {
        return ComponentItemDto::fromComponentName(new self($name, $initialContentsEnum));
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public static function fromNameAndInitialContentEnumWithValue(
        string|DerivedComponentEnum $name,
        InitialContentsEnum $initialContentsEnum,
        null|string $value,
    ): ComponentItemDto {
        return ComponentItemDto::fromComponentName(
            componentName: self::withKeyInitialContentValue(
                name           : $name,
                initialContents: $initialContentsEnum,
                keyOrNameValue : $value,
            ),
        );
    }

    /**
     * Item makes sure it is validated during the Bakame Class
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function getItem(): Item
    {
        try {
            return Item::fromHttpValue($this->toString());
        } catch (Exception $exception) {
            throw new SignatureExtractionException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * important parser for the signature component part
     *
     */
    public function toString(): string
    {
        $parts = [];

        $hasInitialContents = $this->initialContents instanceof InitialContentsEnum;

        $nName = $this->name instanceof DerivedComponentEnum ? $this->name->value : $this->name;

        $nName = !UnquoteHelper::startsWithQuote($nName) ? UnquoteHelper::quote($nName) : $nName;

        $parts[] = $nName;

        if ($hasInitialContents) {
            $parts[] = ';'.$this->initialContents->name;
        }

        if (null !== $this->keyOrNameValue) {
            $parts[] = '='.UnquoteHelper::quote($this->keyOrNameValue);
        }

        return implode('', $parts);
    }

}
