<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Support;

use Filefabrik\Rfc9421\Dto\ComponentItemDto;
use Filefabrik\Rfc9421\Signature\ComponentName;
use Filefabrik\Rfc9421\Signature\DerivedComponentEnum;
use Filefabrik\Rfc9421\Signature\InitialContentsEnum;

trait SigningHeaderTrait
{
    /**
     * Signing header Parameter
     * todo documentation
     *
     * @param array<int, ComponentItemDto|DerivedComponentEnum|string $headerKeys
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
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
    public function signHeader(
        DerivedComponentEnum|string|ComponentItemDto $headerKey,
    ): self {
        if ($headerKey instanceof ComponentItemDto) {
            $setter = $headerKey;
        } else {
            $setter = ComponentName::fromString($headerKey);
            // the setter

        }

        $this->signatureComponents->set($setter);

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
        $this->signatureComponents->set(ComponentName::fromNameAndInitialContent($name, $initialContentsEnum));

        return $this;
    }
}
