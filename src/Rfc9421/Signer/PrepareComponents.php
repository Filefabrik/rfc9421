<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * todo insert correct copyright
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signer;

use Closure;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421\StructuredFields\Entry;
use Filefabrik\Rfc9421\Support\DefaultPrepareComponentMethodsTrait;
use Filefabrik\Rfc9421\Support\PreparedMethods;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Specific Request|Response Headers has to be prepared
 */
final class PrepareComponents
{
    use DefaultPrepareComponentMethodsTrait;

    public function __construct(
        private(set) readonly RequestInterface|ResponseInterface $message,
        /**
         * Use configuration for later
         *
         */
        private(set) readonly ?SigningConfiguration $signingConfiguration = null,
        /**
         * Prepared Methods are a Stack of closures to process the values in signing-params
         *
         */
        private(set) ?PreparedMethods $preparedMethods = null,
    ) {
    }

    /**
     * @param array<string,Closure> $values
     */
    public function addMappingValues(array $values): self
    {
        $this->getMethodStack()
             ->adds($values)
        ;

        return $this;
    }

    /**
     * use default is nothing was overridden
     *
     */
    private function getMethodStack(): PreparedMethods
    {
        return $this->preparedMethods ??= self::defaultMapperClosure();
    }

    /**
     * todo move to SignerMethods for the requestInterface
     *
     */
    public static function defaultMapperClosure(): PreparedMethods
    {
        return new PreparedMethods()->adds(self::defaultMethodsArray());
    }

    public function setPreparedMethods(PreparedMethods $preparedMethods): self
    {
        $this->preparedMethods = $preparedMethods;

        return $this;
    }

    public function processValue(string $key): int|string|Entry
    {
        return $this->getMethodStack()
                    ->getMethod($key)(
                        $this->message,
                    );
    }

}
