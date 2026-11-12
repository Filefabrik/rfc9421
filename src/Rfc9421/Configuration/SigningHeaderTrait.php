<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Configuration;

use Filefabrik\Rfc9421\Dto\ComponentItemDto;
use Filefabrik\Rfc9421\Enums\DerivedComponentEnum;
use Filefabrik\Rfc9421\Enums\InitialContentsEnum;
use Filefabrik\Rfc9421\Signature\ComponentName;

trait SigningHeaderTrait
{
    /**
     * Signing header Parameter
     * todo documentation
     *
     * @param array<int, ComponentItemDto|DerivedComponentEnum|string> $headerKeys
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function signHeaders(array $headerKeys): self
    {
        foreach ($headerKeys as $headerKey) {
            $this->signHeader($headerKey);
        }

        return $this;
    }

    /**
     * Sign Component pure from header
     * todo, if the code /path?param=value&foo=bar&baz=batman&qux= and the signing wich
     * Todo set the sign header has some rules what is valid.
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function signHeader(DerivedComponentEnum|string|ComponentItemDto $headerKey): self
    {
        $component = $headerKey instanceof ComponentItemDto ? $headerKey : ComponentName::fromString($headerKey);

        $this->setComponent($component);

        return $this;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     */
    private function setComponent(ComponentItemDto $component): self
    {
        $this->signatureComponents->set($component);

        return $this;
    }

    /**
     * todo documentation
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function signHeaderNameInitialContent(
        string|DerivedComponentEnum $name,
        InitialContentsEnum|string $initialContentsEnum,
    ): self {
        $this->setComponent(
            ComponentName::fromNameAndInitialContent(
                name               : $name,
                initialContentsEnum: $initialContentsEnum,
            ),
        );

        return $this;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function signComponentSf(DerivedComponentEnum|string $headerKey): self
    {
        $this->setComponent(ComponentName::withSf(name: $headerKey));

        return $this;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function signComponentKey(
        DerivedComponentEnum|string $headerKey,
        string $keyOrNameValue,
        bool $asReq = false,
    ): self {
        $this->setComponent(
            ComponentName::withKey(
                name   : $headerKey,
                keyName: $keyOrNameValue,
                asReq  : $asReq,
            ),
        );

        return $this;
    }

    /**
     * Only the "component-name"
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     */
    public function signComponent(string|DerivedComponentEnum $headerKey, bool $asReq = false): self
    {
        return $this->setComponent(
            ComponentName::withComponent(
                name : $headerKey,
                asReq: $asReq,
            ),
        );
    }
}
