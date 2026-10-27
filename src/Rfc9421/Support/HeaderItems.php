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
     * @param array{string:string}|array{} $lowerCasedHeaders
     */
    public function __construct(private array $lowerCasedHeaders = [])
    {
    }

    public static function fromInterface(MessageInterface $message): self
    {
        $normalizedHeader = array_map(fn(array $value): string => implode(', ', $value), $message->getHeaders());

        return self::fromArray($normalizedHeader);
    }

    /**
     * @param array{string:string} $headerItems
     */
    public static function fromArray(array $headerItems): self
    {
        $instance = new self();
        foreach ($headerItems as $name => $value) {
            $instance->add($name, $value);
        }

        return $instance;
    }

    private function add(string $name, string $value = ''): void
    {
        $this->lowerCasedHeaders[$this->normalizeName($name)] = $value;
    }

    /**
     * @return array{string:string}|array{}
     */
    public function toArray(): array
    {
        return $this->lowerCasedHeaders;
    }

}
