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

use Filefabrik\Rfc9421\Signer\Signatures\ReadLines;
use Filefabrik\Rfc9421\Verify\AbstractVerifyPrepare;
use Filefabrik\Rfc9421\Verify\HeaderHelper;
use Psr\Http\Message\ResponseInterface;

/**
 * Verify a signed Response
 */
final class VerifyResponsePrepare extends AbstractVerifyPrepare
{
    /**
     * If Signed Response and is there inside an ';req' inject the line from your request to check it
     */
    private ?ReadLines $expectedSigners = null;

    public function __construct(
        private(set) readonly ResponseInterface $message,
        private(set) readonly SigningConfiguration $signingConfiguration,
    ) {
        $this->headerHelper = new HeaderHelper($this->message);
    }

    public function setExpectedReqSigners(ReadLines $expectedSignedLines): self
    {
        $this->expectedSigners = $expectedSignedLines;

        return $this;
    }

    public function getExpectedSigners(): ReadLines
    {
        return $this->expectedSigners;
    }
}
