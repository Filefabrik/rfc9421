<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Methods;

use Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException;
use Filefabrik\Rfc9421\StructuredFields\Entry;
use Filefabrik\Rfc9421\Support\UnquoteHelper;
use Filefabrik\Rfc9421\Support\UriHelper;
use Psr\Http\Message\MessageInterface;

final class QueryParams
{
    /**
     * Todo move out to message stack
     *
     * Find one query parameter by name (which must be supplied as parameters in the covered field list).
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     */
    public static function handle(MessageInterface $message, string $fieldName): Entry
    {
        $queryString = UriHelper::messageGetUriHelper($message)
                                ->getQuery()
        ;
        if ('' !== $queryString) {
            $queryParams = self::parseQueryString($queryString);

            // todo, wenn not exists $queryParams[$fieldName], check what is expected
            $value = '';
            if (is_array($queryParams) && isset($queryParams[$fieldName]) && '' !== $queryParams[$fieldName]) {
                $value = UnquoteHelper::quote($queryParams[$fieldName]);
            }

            return new Entry($fieldName, $value)->withKeyMethod(fn ($key): string => UnquoteHelper::quotedUnderscore($key));
        }

        throw new UnProcessableSignatureException('Query string named parameter not set');
    }

    /**
     * todo move out into the method-stack
     *
     * @return non-empty-array<string, string>|null
     *
     * Should not use PHP's parse_str function here, as it has some issues with
     * spaces and dots in parameter names. The following function treats them as
     * opaque strings rather than as variable names.
     */
    private static function parseQueryString(string $query): array|null
    {
        if ('' === $query) {
            return null;
        }

        $result      = [];
        $queryParams = explode('&', $query);
        foreach ($queryParams as $param) {
            // The '=' character is not required and indicates a boolean true value if unset.
            $element                        = explode('=', $param, 2);
            $result[urldecode($element[0])] = isset($element[1]) ? urldecode($element[1]) : '';
        }

        return $result;
    }

}
