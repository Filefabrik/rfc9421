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
use Filefabrik\Rfc9421\Signature\SigningComponents;

trait SigningHeaderTrait
{
    private ?SigningComponents $signatureComponents = null {
        get => $this->signatureComponents ??= new SigningComponents();
    }

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
    public function signComponentSf(DerivedComponentEnum|string $headerKey, bool $asReq = false): self
    {
        return $this->setComponent(ComponentName::withSf(name: $headerKey, asReq: $asReq));
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function signComponentKey(
        DerivedComponentEnum|string $headerKey,
        string $keyValue,
        bool $asReq = false,
    ): self {
        $this->setComponent(
            ComponentName::withKey(
                name    : $headerKey,
                keyValue: $keyValue,
                asReq   : $asReq,
            ),
        );

        return $this;
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function signComponentName(
        DerivedComponentEnum|string $headerKey,
        string $nameValue,
        bool $asReq = false,
    ): self {
        $this->setComponent(
            ComponentName::withName(
                name     : $headerKey,
                nameValue: $nameValue,
                asReq    : $asReq,
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

    public function getSignatureComponents(): SigningComponents
    {
        return $this->signatureComponents;
    }
}
