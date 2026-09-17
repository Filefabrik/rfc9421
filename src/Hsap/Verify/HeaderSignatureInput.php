<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Verify;

use Filefabrik\Hsap\Exceptions\VerifyHeaderSignatureException;
use Filefabrik\Hsap\Extractors\SigningFields;

final readonly class HeaderSignatureInput
{
    public function __construct(
        public private(set) string $headerSignatureInputValue,
        public private(set) string $signatureNameKey,
    ) {
    }

    public function extract(): array
    {
        if ($this->signatureNameKey) {
            if (!str_starts_with($this->headerSignatureInputValue, $this->signatureNameKey)) {
                throw VerifyHeaderSignatureException::invalidSignatureKeyName($this->signatureNameKey,$this->headerSignatureInputValue);
            }
            $raw = substr($this->headerSignatureInputValue, strlen($this->signatureNameKey));
        } else {
            $raw = $this->headerSignatureInputValue;
        }
        // todo validate starts and ends with regular braces ()
        // validate against more then on space between
        return new SigningFields($raw)->toArray();
    }
}
