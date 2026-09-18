<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Support;

abstract class AbstractMetadata
{
    /**
     * while to string make key value pairs quoted "
     *
     * @var string[]
     */
    public static array $propertiesToQuote = ['nonce', 'keyid', 'alg', 'tag'];

    /**
     * During decode from string/incoming request and this keys exists in Metadata. cast them to integer
     *
     * @var string[]
     */
    protected static array $decodeToInt = ['created', 'expires'];
}
