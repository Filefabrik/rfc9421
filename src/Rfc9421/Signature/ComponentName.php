<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
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
         * todo check against quotas
         */
        private(set) readonly ?string $keyOrNameValue = null,
        /**
         * Req Parameter
         *
         * @var bool
         */
        private readonly bool $asReq = false,
    ) {
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    private function setValidatedName(string $name): string|DerivedComponentEnum
    {
        // todo validate value against unwanted chars/expressions
        $lower = strtolower(trim($name));

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
        foreach (['extract4', 'extract3', 'extract2'] as $method) {
            $result = call_user_func([self::class, $method], $segment);
            if ($result instanceof self) {
                return $result;
            }
        }

        if (!str_contains($segment, ';')) { // else normal format
            return new ComponentName($segment);
        }

        throw new SignatureExtractionException(sprintf('Segment has unknown format "%s"', $segment));
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
    public static function withSf(string|DerivedComponentEnum $name, bool $asReq = false): ComponentItemDto
    {
        return self::fromNameAndInitialContent(
            name               : $name,
            initialContentsEnum: InitialContentsEnum::sf,
            asReq              : $asReq,
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public static function fromNameAndInitialContent(
        string|DerivedComponentEnum $name,
        InitialContentsEnum|string $initialContentsEnum,
        bool $asReq = false,
    ): ComponentItemDto {
        return ComponentItemDto::fromComponentName(
            new self(
                name           : $name,
                initialContents: $initialContentsEnum,
                asReq          : $asReq,
            ),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public static function withBs(string|DerivedComponentEnum $name, bool $asReq = false): ComponentItemDto
    {
        return self::fromNameAndInitialContent(
            name               : $name,
            initialContentsEnum: InitialContentsEnum::bs,
            asReq              : $asReq,
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @example with "COMPONENT-NAME";name="VALUE"
     * @example with "COMPONENT-NAME";req;name="VALUE"
     */
    public static function withName(
        string|DerivedComponentEnum $name,
        ?string $nameValue = null,
        bool $asReq = false,
    ): ComponentItemDto {
        return self::fromNameAndInitialContentEnumWithValue(
            name               : $name,
            initialContentsEnum: InitialContentsEnum::name,
            keyOrNameValue     : $nameValue,
            asReq              : $asReq,
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public static function fromNameAndInitialContentEnumWithValue(
        string|DerivedComponentEnum $name,
        InitialContentsEnum $initialContentsEnum,
        null|string $keyOrNameValue,
        bool $asReq = false,
    ): ComponentItemDto {
        return ComponentItemDto::fromComponentName(
            componentName: self::withKeyInitialContentValue(
                name           : $name,
                initialContents: $initialContentsEnum,
                keyOrNameValue : $keyOrNameValue,
                asReq          : $asReq,
            ),
        );
    }

    /**
     * 3-Parts such as '"example-dict";key="a"'
     */
    private static function withKeyInitialContentValue(
        string|DerivedComponentEnum $name,
        string|InitialContentsEnum $initialContents,
        string|null $keyOrNameValue,
        bool $asReq = false,
    ): self {
        return new ComponentName(
            name           : $name,
            initialContents: $initialContents,
            keyOrNameValue : $keyOrNameValue,
            asReq          : $asReq,
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @example with "COMPONENT-NAME";req;key="VALUE"
     * @example with "COMPONENT-NAME";key="VALUE"
     */
    public static function withKey(
        string|DerivedComponentEnum $name,
        ?string $keyName = null,
        bool $asReq = false,
    ): ComponentItemDto {
        return self::fromNameAndInitialContentEnumWithValue(
            name               : $name,
            initialContentsEnum: InitialContentsEnum::key,
            keyOrNameValue     : $keyName,
            asReq              : $asReq,
        );
    }

    /**
     * Only the component key perhaps the ';req'
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public static function withComponent(string|DerivedComponentEnum $name, bool $asReq = false): ComponentItemDto
    {
        return ComponentItemDto::fromComponentName(new self(name: $name, asReq: $asReq));
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private static function extract4(string $segment): ?self
    {
        $s       = implode('|', [InitialContentsEnum::key->name, InitialContentsEnum::name->name]);
        $pattern = '^"(.*?)";('.$s.')="(.*?)"$';
        preg_match('#'.$pattern.'#', $segment, $matches);

        return (4 === count($matches)) ?
            self::withKeyInitialContentValue(
                name           : $matches[1],
                initialContents: $matches[2],
                keyOrNameValue : $matches[3],
            ) : null;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private static function extract3(string $segment): ?self
    {
        // todo can be with ;asReq
        $s = implode('|', InitialContentsEnum::names());
        preg_match('#^"(.*?)";('.$s.')$#', $segment, $matches);

        return 3 === count($matches) ? new self($matches[1], $matches[2]) : null;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private static function extract2(string $segment): ?self
    {
        $simple = '#^"(.*?)"$#';
        preg_match($simple, $segment, $matches);

        return 2 === count($matches) ? new self($matches[1]) : null;
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
     * Build a clear expression from the parts
     */
    public function toString(): string
    {
        $parts = [];

        $hasInitialContents = $this->initialContents instanceof InitialContentsEnum;

        $nName = $this->name instanceof DerivedComponentEnum ? $this->name->value : $this->name;

        $nName = !UnquoteHelper::startsWithQuote($nName) ? UnquoteHelper::quote($nName) : $nName;

        $parts[] = $nName;
        // with the ';req'
        if ($this->asReq) {
            $parts[] = ';'.InitialContentsEnum::req->name;
        }
        if ($hasInitialContents) {
            $parts[] = ';'.$this->initialContents->name;
            // only if we have initial contents enum
            if (null !== $this->keyOrNameValue) {
                $parts[] = '='.UnquoteHelper::quote($this->keyOrNameValue);
            }
        }

        return implode('', $parts);
    }

}
