<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signature;

enum DerivedComponentEnum: string
{
    /*
     * @signature-params	Reserved for signature parameters line in signature base	Active	Request, Response	Section 2.3 of RFC 9421
     */
    case signatureParams = '@signature-params';
    /*
     * @method	The HTTP request method	Active	Request	Section 2.2.1 of RFC 9421
     */
    case method = '@method';
    /*
     * @authority	The HTTP authority, or target host	Active	Request	Section 2.2.3 of RFC 9421
     */
    case authority = '@authority';
    /*
     * @scheme	The URI scheme of the request URI	Active	Request	Section 2.2.4 of RFC 9421
     */
    case scheme = '@scheme';
    /*
     * @target-uri	The full target URI of the request	Active	Request	Section 2.2.2 of RFC 9421
     */
    case targetUri = '@target-uri';
    /*
     * @request-target	The request target of the request	Active	Request	Section 2.2.5 of RFC 9421
     */
    case requestTarget = '@request-target';
    /*
     * @path	The full path of the request URI	Active	Request	Section 2.2.6 of RFC 9421
    */
    case path = '@path';
    /*
     * @query	The full query of the request URI	Active	Request	Section 2.2.7 of RFC 9421
     */
    case query = '@query';
    /*
     * @query-param	A single named query parameter	Active	Request	Section 2.2.8 of RFC 9421
     */
    case queryParam = '@query-param';
    /*
     * @status	The status code of the response	Active	Response	Section 2.2.9 of RFC 9421
     */
    case status = '@status';

    public static function isOwnValueOrNull(string|self $value): self|null
    {
        if ($value instanceof self) {
            return $value;
        }

        return array_find(self::cases(), fn ($item): bool => $item->value === $value);
    }
}
