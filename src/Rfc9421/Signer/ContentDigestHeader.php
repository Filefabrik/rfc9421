<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signer;

use Filefabrik\Rfc9421\Enums\HeaderKeysEnum;
use Filefabrik\Rfc9421\Exceptions\ContentDigestException;
use Filefabrik\Rfc9421\SigningConfiguration;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * inserting the content-digest into header
 */
final readonly class ContentDigestHeader
{
    public function __construct(
        private(set) RequestInterface|ResponseInterface $message,
        private(set) SigningConfiguration $signingConfiguration,
    ) {
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException

     */
    public function handle():RequestInterface|ResponseInterface
    {
        if ($this->message->hasHeader(HeaderKeysEnum::content_digest->value)) {
            // throw already set content-digest
            throw ContentDigestException::headerContentDigestAlreadySet();
        }

        if (!$this->signingConfiguration->hasContentDigestHashAlgo()) {
            throw ContentDigestException::canNotHashWithoutAlgorithm();
        }

        // set the body digest into the header
        [$header,$value]= new SignBody(
            body     : (string)$this->message->getBody(),
            algorithm: $this->signingConfiguration->getContentDigestHashAlgoOrThrow(),
        )->getContentDigestHeaderArray();
        return $this->message->withHeader($header, $value);
    }

}
