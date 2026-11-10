<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421;

use Filefabrik\Rfc9421\Verify\AbstractVerifyPrepare;
use Filefabrik\Rfc9421\Verify\HeaderHelper;
use Psr\Http\Message\RequestInterface;

/**
 * Outside from the verify process read only the incoming request
 * After the prepareVerification method call, the request was fully "decoded"
 */
final class VerifyRequestPrepare extends AbstractVerifyPrepare
{
    public function __construct(
        private(set) readonly RequestInterface $message,
        private(set) readonly SigningConfiguration $signingConfiguration,
    ) {
        $this->headerHelper = new HeaderHelper($this->message);
    }

}
