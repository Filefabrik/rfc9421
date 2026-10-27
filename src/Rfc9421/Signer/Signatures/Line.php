<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signer\Signatures;

/**
 * Signature line, the si
 */
final readonly class Line
{
    /**
     * @param string $signatureComponent the component name "@method"
     * @param string|int $valueToSign the extracted value representation "@method": POST
     */
    public function __construct(private(set) string $signatureComponent, private(set) string|int $valueToSign)
    {
    }

}
