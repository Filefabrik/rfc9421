<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signer\Signatures;

/**
 * Signature line, the si
 */
final readonly class Line
{
    /**
     * @param string $signatureComponent the component name "@method" Note: The Signature Component has no function atm
     * @param string $valueToSign the extracted value representation "@method": POST
     */
    public function __construct(private(set) string $signatureComponent, private(set) string $valueToSign)
    {
    }

}
