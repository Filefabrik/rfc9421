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

use Bakame\Http\StructuredFields\Bytes;
use Bakame\Http\StructuredFields\DisplayString;
use Bakame\Http\StructuredFields\Token;
use DateTimeImmutable;
use Filefabrik\Rfc9421\Enums\InitialContentsEnum;
use Filefabrik\Rfc9421\Support\UnquoteHelper;

final readonly class RenderInitialContents
{

    /**
     * @param array<InitialContentsEnum> $withValues They need a value later in fieldName="expect_value" key="expect_value"
     */
    public function __construct(
        private InitialContentsParameters $initialContentsParameters,
        private array  $withValues=[InitialContentsEnum::key, InitialContentsEnum::name],
    ) {
    }

    /**
     * @return array<string>
     */
    public function toArray(): array
    {
        /*
         * @see https://www.rfc-editor.org/info/rfc9421/#section-2.1.3
         * todo bs allowed with some byte sequences (base_encode64)
         * Example-Header: value, with, lots
         * Example-Header: of, commas
         * "example-header";bs: :dmFsdWUsIHdpdGgsIGxvdHM=:, :b2YsIGNvbW1hcw==:
         */
        /*
         * @see https://www.rfc-editor.org/info/rfc9421/#section-2.1.1-8
         * "However, if the sf parameter is added, the value is reserialized as follows:"
         * "example-dict";sf: a=1, b=2;x=1;y=2, c=(a b c)
         * "example-header";bs: :dmFsdWUsIHdpdGgsIGxvdHMsIG9mLCBjb21tYXM=:
         */

        $parts = [];
        foreach ($this->initialContentsParameters->parameters as $key => $value) {
            /** @var InitialContentsEnum $enum */
            $enum = InitialContentsEnum::isOwnTypeOrNull($key);
            if (in_array($enum, $this->withValues, true)) {
                $parts[] = implode('', $this->handleKeyOrName($enum, $value));
            } else {
                $parts[] = $enum->name;
            }
        }

        return $parts;
    }

    /**
     * Special Method which is adding the `fieldName="$this->nameOrKeyValue"` or `key="$this->nameOrKeyValue"`
     *
     *
     * @return string[]
     */
    private function handleKeyOrName(
        InitialContentsEnum $initialContentsEnum,
        Bytes|DisplayString|Token|bool|DateTimeImmutable|float|int|string|null $keyOrValue,
    ): array {
        return [$initialContentsEnum->name, ComponentBuilder::equals, UnquoteHelper::quote((string)$keyOrValue)];
    }

}
