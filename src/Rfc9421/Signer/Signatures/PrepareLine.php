<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signer\Signatures;

use Filefabrik\Rfc9421\Dto\ComponentItemDto;
use Filefabrik\Rfc9421\Dto\HeaderItemsDto;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421\StructuredFields\DestructEntry;
use Psr\Http\Message\MessageInterface;

/**
 * prepares a single Line
 */
final readonly class PrepareLine
{
    public function __construct(
        private(set) SigningConfiguration $signingConfiguration,
        private(set) ComponentItemDto $componentItemDto,
        private(set) MessageInterface $message,
    ) {
    }

    /**
     * @return \Filefabrik\Rfc9421\Signer\Signatures\Line
     * @throws \Bakame\Http\StructuredFields\StructuredFieldError
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     */
    public function handle(): Line
    {
        // todo getting signature component which is compiling
        $destruct = new DestructEntry(
            componentItemDto    : $this->componentItemDto,
            signingConfiguration: $this->signingConfiguration,
            headerItemsDto      : HeaderItemsDto::fromMessageInterface($this->message),
            messageInterface    : $this->message,
        );
        $entry    = $destruct->handle();

        return new Line(
            signatureComponent: $this->componentItemDto->getHttpValue(),
            valueToSign       : $entry->parse(),
        );
    }

}
