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

use Filefabrik\Rfc9421\Dto\ComponentItemDto;
use Filefabrik\Rfc9421\Dto\HeaderItemsDto;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421\StructuredFields\CompileEntry;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * prepares a single Line
 * todo tell what prepare line means
 */
final readonly class PrepareLine
{
    public function __construct(
        private(set) SigningConfiguration $signingConfiguration,
        private(set) ComponentItemDto $componentItemDto,
        private(set) RequestInterface|ResponseInterface $message,
    ) {
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function handle(): Line
    {
        // todo getting signature component which is compiling
        $destruct = new CompileEntry(
            messageInterface    : $this->message,
            signingConfiguration: $this->signingConfiguration,
            componentItemDto    : $this->componentItemDto,
            headerItemsDto      : HeaderItemsDto::fromMessageInterface($this->message),
        );
        $entry    = $destruct->handle();

        return new Line(
            signatureComponent: $this->componentItemDto->getComponent(),
            valueToSign       : $entry->getStringValue(),
        );
    }

}
