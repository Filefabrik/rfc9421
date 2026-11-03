<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signer;

use Closure;
use Filefabrik\Rfc9421\Methods\MessageAuthority;
use Filefabrik\Rfc9421\Methods\MessagePath;
use Filefabrik\Rfc9421\Methods\MessageQueryParams;
use Filefabrik\Rfc9421\Methods\MessageScheme;
use Filefabrik\Rfc9421\Methods\MessageTargetUri;
use Filefabrik\Rfc9421\Methods\MesssageRequestTarget;
use Filefabrik\Rfc9421\Signature\DerivedComponentEnum;
use Filefabrik\Rfc9421\Signature\InitialContentsEnum;
use Filefabrik\Rfc9421\Signature\InitialContentsParameters;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421\StructuredFields\Entry;
use Filefabrik\Rfc9421\Support\PreparedMethods;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use UnexpectedValueException;

/**
 * Specific Request|Response Headers has to be prepared
 */

/**
 * todo closure processing
 */
final class PrepareComponents
{
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
     * @param array{string:Closure} $values
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
        /*
         * for different components there can be different methods to prepare the values(upper-case lower-case )
         */
        return new PreparedMethods()
            /*
             * extracting the "method" from header to use later as "@method"
             *
             * @see 2.2.1. Method
             */
            ->add(
                key    : DerivedComponentEnum::method,
                closure: fn (RequestInterface $messageInterface): Entry => new Entry(
                    DerivedComponentEnum::method->value,
                    strtoupper($messageInterface->getMethod()),
                ),
            )
            ->add(
                key    : DerivedComponentEnum::targetUri,
                closure: fn (RequestInterface $messageInterface): Entry => new Entry(
                    key  : DerivedComponentEnum::targetUri->value,
                    value: MessageTargetUri::handle($messageInterface),
                ),
            )
            /*
             * @see 2.2.3. Authority
             */
            ->add(
                key    : DerivedComponentEnum::authority,
                closure: fn (RequestInterface $messageInterface): Entry => new Entry(
                    DerivedComponentEnum::authority->value,
                    MessageAuthority::handle($messageInterface),
                ),
            )
            /*
             * @see 2.2.4. Scheme
             */
            ->add(
                key    : DerivedComponentEnum::scheme,
                closure: fn (RequestInterface $messageInterface): Entry => new Entry(
                    key  : DerivedComponentEnum::scheme->value,
                    value: MessageScheme::handle($messageInterface),
                ),
            )
            /*
             * 2.2.5. Request Target
             */
            ->add(
                key    : DerivedComponentEnum::requestTarget,
                closure: fn (RequestInterface $messageInterface): Entry => new Entry(
                    key  : DerivedComponentEnum::requestTarget->value,
                    value: MesssageRequestTarget::handle($messageInterface),
                ),
            )
            /*
             * @see 2.2.6. Path
             */
            ->add(
                key    : DerivedComponentEnum::path,
                closure: fn (RequestInterface $messageInterface): Entry => new Entry(
                    key  : DerivedComponentEnum::path->value,
                    value: MessagePath::handle($messageInterface),
                ),
            )/*
             * @see 2.2.9. Status Code
             */
            ->add(
                key    : DerivedComponentEnum::status,
                closure: fn (ResponseInterface $response): Entry => new Entry(
                    DerivedComponentEnum::status->value,
                    (string)$response->getStatusCode(),
                ),
            )
            /*
             * @2.2.8. Query Parameters
             */
            ->add(
                key    : DerivedComponentEnum::queryParam,
                closure: fn (
                    MessageInterface $messageInterface,
                    InitialContentsParameters $parameters,
                ): Entry => MessageQueryParams::handle(
                    message  : $messageInterface,
                    fieldName: $parameters->getValueOrThrow(InitialContentsEnum::name),
                ),
            )/*
             * @see 2.2.7. Query
             */
            ->add(
                key    : DerivedComponentEnum::query,
                closure: fn (RequestInterface $messageInterface): Entry => new Entry(
                    key  : DerivedComponentEnum::query->value,
                    value: $messageInterface->getUri()
                                            ->getQuery(),
                ),
            )
            ->add(
                key    : 'content-length',
                closure: fn (RequestInterface|ResponseInterface $message): Entry => new Entry(
                    key  : 'content-length',
                    value: strlen((string)$message->getBody()),
                ),
            )
            /*
             * @see 2.3. Signature Parameters @Important after Processing all other components
             */
            ->add(
                key    : DerivedComponentEnum::signatureParams,
                closure: fn () => throw new UnexpectedValueException(
                    'remove before processing signatureParams',
                ),
            )
        ;
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
