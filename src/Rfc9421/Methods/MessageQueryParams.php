<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Methods;

use Bakame\Http\StructuredFields\Bytes;
use Bakame\Http\StructuredFields\DisplayString;
use Bakame\Http\StructuredFields\Token;
use DateTimeImmutable;
use Filefabrik\Rfc9421\Enums\DerivedComponentEnum;
use Filefabrik\Rfc9421\Exceptions\QueryParamsException;
use Filefabrik\Rfc9421\Signature\ComponentBuilder;
use Filefabrik\Rfc9421\StructuredFields\EntryWithValue;
use Filefabrik\Rfc9421\Support\MessageInterfaceHelper;
use Psr\Http\Message\MessageInterface;

final class MessageQueryParams
{
    /**
     * Todo comment
     *
     * Find one query parameter by fieldName (which must be supplied as parameters in the covered field list).
     *
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\QueryParamsException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public static function handle(MessageInterface $message, Bytes|DisplayString|Token|bool|DateTimeImmutable|float|int|string|null $fieldName): EntryWithValue
    {
        $queryString = MessageInterfaceHelper::hasGetUri($message)
                                             ->getQuery()
        ;
        if ('' === $queryString) {
            throw QueryParamsException::emptyQueryString();
        }

        $queryParams = self::parseQueryString($queryString);

        // todo, wenn not exists $queryParams[$fieldNameDto], check what is expected
        $value = '';
        if (array_key_exists($fieldName, $queryParams) && '' !== $queryParams[$fieldName]) {
            $fValue = $queryParams[$fieldName];
            // multiline or problematic chars
            $value = rawurlencode($fValue);
        }

        /*
         * Make "qux" trailing space will be added during compile with Entry->parse()
         * "@query-param";fieldName="baz": batman
         * "@query-param";fieldName="qux":
         * "@query-param";fieldName="param": value
         */
        //DerivedComponentEnum::queryParam->value
        /*
         * todo with key method is potentially wrong in 'macgirvin/http-message-signer' no underscores needed
         */

        // InitialContentsEnum::fieldName, $fieldName
        // can be pure because the Name-Class fix the InitialContentsEnum::fieldName, $fieldName
        return EntryWithValue::fromBuilder(
            builder: ComponentBuilder::from(name: DerivedComponentEnum::queryParam)
                                     ->withName(value: $fieldName),
            value  : $value,
        );
        //   return new Entry(DerivedComponentEnum::queryParam->value, $value, InitialContentsEnum::name, $fieldName);
    }

    /**
     * Todo extremely buggy
     * todo comment
     * todo user should use an own security parser. Make documentation of MessageQueryParams override
     * The value of the fieldName parameter and the component value of a single named parameter are calculated via the following process:
     *
     * Parse the nameString or valueString of the named query parameter defined by Section 5.1 ("application/x-www-form-urlencoded parsing") of [HTMLURL]; this is the value after percent-encoded octets are decoded.
     * Encode the nameString or valueString using the "percent-encode after encoding" process defined by Section 5.2 ("application/x-www-form-urlencoded serializing") of [HTMLURL]; this results in an ASCII string [ASCII].
     * Output the ASCII string.
     * GET /parameters?var=this%20is%20a%20big%0Amultiline%20value&\
     * bar=with+plus+whitespace&fa%C3%A7ade%22%3A%20=something HTTP/1.1
     * "@query-param";fieldName="var": this%20is%20a%20big%0Amultiline%20value
     * "@query-param";fieldName="bar": with%20plus%20whitespace
     * "@query-param";fieldName="fa%C3%A7ade%22%3A%20": something
     *
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\QueryParamsException
     * @return non-empty-array<string, string>
     *
     * Should not use PHP's parse_str function here, as it has some issues with
     * spaces and dots in parameter names. The following function treats them as
     * opaque strings rather than as variable names.
     */
    public static function parseQueryString(string $query): array
    {
        $result      = [];
        $queryParams = explode('&', $query);
        foreach ($queryParams as $param) {
            // The '=' character is not required and indicates a boolean true value if unset.
            [$key, $value] = explode('=', $param, 2);

            $KeyDecoded   = rawurldecode($key);
            $KeyReEncoded = rawurlencode($KeyDecoded);

            if ($KeyReEncoded !== $key) {
                throw QueryParamsException::keyException($key);
            }

            $ValueDecoded = urldecode($value);
            /* $ValueReEncoded = urlencode($ValueDecoded);

             if ($ValueReEncoded !== $value) {
               //  throw QueryParamsException::valueException($value);
             }*/

            // fa%C3%A7ade%22%3A%20
            $result[$KeyReEncoded] = $ValueDecoded;
            // $result[rawurldecode($element[0])] = isset($element[1]) ? urldecode($element[1]) : '';
        }

        return $result;
    }
}
