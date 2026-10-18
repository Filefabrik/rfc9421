<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\StructuredFields;

use JsonException;
use UnexpectedValueException;

final class StructuredFields
{
    private static string $list = <<<JSON
        {
          "accept": "list",
          "accept-encoding": "list",
          "accept-language": "list",
          "accept-patch": "list",
          "accept-post": "list",
          "accept-ranges": "list",
          "access-control-allow-credentials": "item",
          "access-control-allow-headers": "list",
          "access-control-allow-methods": "list",
          "access-control-allow-origin": "item",
          "access-control-expose-headers": "list",
          "access-control-max-age": "item",
          "access-control-request-headers": "list",
          "access-control-request-method": "item",
          "age": "item",
          "allow": "list",
          "alpn": "list",
          "alt-svc": "dictionary",
          "alt-used": "item",
          "cache-control": "dictionary",
          "cdn-loop": "list",
          "clear-site-data": "list",
          "connection": "list",
          "content-encoding": "list",
          "content-language": "list",
          "content-length": "list",
          "content-location": "url",
          "content-type": "item",
          "cookie": "cookie",
          "cross-origin-resource-policy": "item",
          "date": "date",
          "dnt": "item",
          "etag": "etag",
          "expect": "dictionary",
          "expect-ct": "dictionary",
          "expires": "date",
          "host": "item",
          "if-match": "etag",
          "if-modified-since": "date",
          "if-none-match": "etag",
          "if-unmodified-since": "date",
          "keep-alive": "dictionary",
          "last-modified": "date",
          "location": "url",
          "max-forwards": "item",
          "origin": "item",
          "pragma": "dictionary",
          "prefer": "dictionary",
          "preference-applied": "dictionary",
          "referer": "url",
          "retry-after": "item",
          "sec-websocket-extensions": "list",
          "sec-websocket-protocol": "list",
          "sec-websocket-version": "item",
          "server-timing": "list",
          "set-cookie": "cookie",
          "surrogate-control": "dictionary",
          "te": "list",
          "timing-allow-origin": "list",
          "trailer": "list",
          "transfer-encoding": "list",
          "upgrade-insecure-requests": "item",
          "vary": "list",
          "x-content-type-options": "item",
          "x-frame-options": "item",
          "x-xss-protection": "list"
        }      
        JSON;

    public function __construct(private(set) array $fields = [])
    {
    }

    /**
     * @throws JsonException
     */
    public static function fromDefaultList(): self
    {
        return new self(self::getDefaultList());
    }

    /**
     * @throws JsonException
     * @return array{string:string}
     */
    public static function getDefaultList(): array
    {
        return json_decode(self::$list, true, 512, JSON_THROW_ON_ERROR);
    }

    public function addMany(array $fields): self
    {
        foreach ($fields as $key => $value) {
            $this->add($key, $value);
        }

        return $this;
    }

    public function add(string $key, string $value): self
    {
        $this->fields[$key] = $value;

        return $this;
    }

    public function getOrNull(string $key): ?string
    {
        return $this->fields[$key] ?? null;
    }

    public function getOrThrow(string $key): string
    {
        return $this->fields[$key] ?? throw new UnexpectedValueException(sprintf("Field '%s' does not exist.", $key));
    }

}
