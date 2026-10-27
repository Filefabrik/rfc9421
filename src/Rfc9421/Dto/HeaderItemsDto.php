<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Dto;

use Filefabrik\Rfc9421\Support\HeaderItems;
use Filefabrik\Rfc9421\Support\LowerCaseHeaderTrait;
use Psr\Http\Message\MessageInterface;

/**
 * Use Dto for prepared and validated stuff
 */
final readonly class HeaderItemsDto
{
    use LowerCaseHeaderTrait;

    /**
     * @param array{string:string}|array{} $lowerCasedHeaders
     */
    private function __construct(private(set) array $lowerCasedHeaders = [])
    {
    }

    /**
     * bridge durch den validator
     */
    public static function fromArray(array $headerItems): self
    {
        return self::fromHeaderItems(HeaderItems::fromArray($headerItems));
    }

    public static function fromHeaderItems(HeaderItems $headerItems): self
    {
        return new self($headerItems->toArray());
    }

    public static function fromMessageInterface(MessageInterface $message): self
    {
        return self::fromHeaderItems(HeaderItems::fromInterface($message));
    }
}
