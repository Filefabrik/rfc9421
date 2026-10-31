<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Support;

use Psr\Http\Message\MessageInterface;

/**
 * for internal usage
 */
final class HeaderItems
{
    use LowerCaseHeaderTrait;

    /**
     * @var array<string,string>|array{}
     */
    private array $lowerCasedHeaders = [];

    /**
     * @param array<string,string>|array{} $lowerCasedHeaders
     */
    public function __construct(array $lowerCasedHeaders = [])
    {
        $this->addMany($lowerCasedHeaders);
    }

    /**
     * @param array{string:string}|array{} $headerItems
     *
     */
    private function addMany(array $headerItems): self
    {
        foreach ($headerItems as $name => $value) {
            $this->add($name, $value);
        }

        return $this;
    }

    private function add(string $name, string $value = ''): void
    {
        $this->lowerCasedHeaders[$this->normalizeName($name)] = $value;
    }

    public static function fromInterface(MessageInterface $message): self
    {
        /** @var array<string,string> $normalizedHeader */
        $normalizedHeader = array_map(fn (array $value): string => implode(', ', $value), $message->getHeaders());

        return self::fromArray($normalizedHeader);
    }

    /**
     * @param array<string,string> $headerItems
     */
    public static function fromArray(array $headerItems): self
    {
        return new self()->addMany($headerItems);
    }

    /**
     * @return array<string,string> |array{}
     */
    public function toArray(): array
    {
        return $this->lowerCasedHeaders;
    }

}
