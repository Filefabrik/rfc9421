<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signature;

/**
 * Single component with InitialContentsEnum or without InitialContentsEnum
 */
final class Component
{
    public function __construct(
        /**
         * @var string $name name of the component @authority|content-length
         */
        private(set) string $name {
            set => strtolower($value);
        },
        null|InitialContentsEnum|string $initialContentsEnum = null {
            set => $this->initialContentsEnum = InitialContentsEnum::isOwnTypeOrNullOrThrow($value);
        },
    ) {
    }
}
