<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signature;

use Filefabrik\Rfc9421\Exceptions\EnumTypeException;

/**
 * Single component with InitialContentsEnum or without InitialContentsEnum
 */
final class Component
{
    public function __construct(
        /**
         * @var string $name name of the component @authority|content-length
         */
        private(set) string|DerivedComponentEnum $name {
            /**
             * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
             */ set =>
            $value instanceof DerivedComponentEnum ?
                $value :
                $this->setValidatedName($value);
        },
        private(set) null|InitialContentsEnum|string $initialContentsEnum = null {
            /**
             * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
             */
            set => InitialContentsEnum::isOwnTypeOrNullOrThrow($value);
        },
    ) {
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     */
    private function setValidatedName(string $name): string|DerivedComponentEnum
    {
        // todo validate value against unwanted chars/expressions
        $lower = strtolower($name);

        if (str_starts_with($lower, '@')) {
            return DerivedComponentEnum::isOwnValueOrNull($lower) ?? throw EnumTypeException::TypeError(
                $lower,
                DerivedComponentEnum::class,
            );
        }

        return $lower;
    }

    /**
     * Real key value @status or content-length or something
     *
     */
    public function getNormalizedName(): string
    {
        return $this->name instanceof DerivedComponentEnum ? $this->name->value : $this->name;
    }

}
