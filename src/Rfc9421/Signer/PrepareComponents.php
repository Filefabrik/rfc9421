<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signer;

use Closure;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421\StructuredFields\EntryWithValue;
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
        if (null === $this->preparedMethods) {
            $this->preparedMethods = self::defaultMapperClosure();
        }

        return $this->preparedMethods;
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

    /**
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function processValue(string $key): EntryWithValue
    {
        return $this->getMethodStack()
                    ->callWith($key, [$this->message])
        ;
    }

}
