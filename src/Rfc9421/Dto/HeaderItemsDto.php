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
     * @var array<string,string>|array{}
     */
    private(set) array $lowerCasedHeaders;

    private function __construct(HeaderItems $headerItems)
    {
        $this->lowerCasedHeaders = $headerItems->toArray();
    }

    /**
     * bridge durch den validator
     *
     * @param array<string,string> $headerItems
     *
     */
    public static function fromArray(array $headerItems): self
    {
        return self::fromHeaderItems(HeaderItems::fromArray($headerItems));
    }

    public static function fromHeaderItems(HeaderItems $headerItems): self
    {
        return new self(headerItems: $headerItems);
    }

    public static function fromMessageInterface(MessageInterface $message): self
    {
        return self::fromHeaderItems(HeaderItems::fromInterface($message));
    }
}
