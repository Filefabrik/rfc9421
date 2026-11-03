<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Support;

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
use Filefabrik\Rfc9421\StructuredFields\Entry;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use UnexpectedValueException;

trait DefaultPrepareComponentMethodsTrait
{
    /**
     * @return Closure[]
     */
    private static function defaultMethodsArray(): array
    {
        /*
         * for different components there can be different methods to prepare the values(upper-case lower-case )
         */
        return [
            /*
             * extracting the "method" from header to use later as "@method"
             *
             * @see 2.2.1. Method
             */
            DerivedComponentEnum::method->value        => fn (RequestInterface $messageInterface): Entry => new Entry(
                key  : DerivedComponentEnum::method->value,
                value: strtoupper($messageInterface->getMethod()),
            ),
            DerivedComponentEnum::method->value        => fn (RequestInterface $messageInterface): Entry => new Entry(
                key  : DerivedComponentEnum::method->value,
                value: strtoupper($messageInterface->getMethod()),
            ),
            DerivedComponentEnum::targetUri->value     => fn (RequestInterface $messageInterface): Entry => new Entry(
                key  : DerivedComponentEnum::targetUri->value,
                value: MessageTargetUri::handle($messageInterface),
            ),

            /*
             * @see 2.2.3. Authority
             */
            DerivedComponentEnum::authority->value     => fn (RequestInterface $messageInterface): Entry => new Entry(
                DerivedComponentEnum::authority->value,
                MessageAuthority::handle($messageInterface),
            ),

            /*
             * @see 2.2.4. Scheme
             */
            DerivedComponentEnum::scheme->value        => fn (RequestInterface $messageInterface): Entry => new Entry(
                key  : DerivedComponentEnum::scheme->value,
                value: MessageScheme::handle($messageInterface),
            ),
            /*
             * 2.2.5. Request Target
             */
            DerivedComponentEnum::requestTarget->value => fn (RequestInterface $messageInterface): Entry => new Entry(
                key  : DerivedComponentEnum::requestTarget->value,
                value: MesssageRequestTarget::handle($messageInterface),
            ),

            /*
             * @see 2.2.6. Path
             */
            DerivedComponentEnum::path->value          => fn (RequestInterface $messageInterface): Entry => new Entry(
                key  : DerivedComponentEnum::path->value,
                value: MessagePath::handle($messageInterface),
            ),

            /*
             * @see 2.2.9. Status Code
             */
            DerivedComponentEnum::status->value        => fn (ResponseInterface $response): Entry => new Entry(
                DerivedComponentEnum::status->value,
                (string)$response->getStatusCode(),
            ),

            /*
             * @2.2.8. Query Parameters
             */
            DerivedComponentEnum::queryParam->value    =>
                fn (
                    MessageInterface $messageInterface,
                    InitialContentsParameters $parameters,
                ): Entry => MessageQueryParams::handle(
                    message  : $messageInterface,
                    fieldName: $parameters->getValueOrThrow(InitialContentsEnum::name),
                ),

            /*
             * @see 2.2.7. Query
             */
            DerivedComponentEnum::query->value         =>
                fn (RequestInterface $messageInterface): Entry => new Entry(
                    key  : DerivedComponentEnum::query->value,
                    value: $messageInterface->getUri()
                                            ->getQuery(),
                ),

            'content-length'                             =>
                fn (RequestInterface|ResponseInterface $message): Entry => new Entry(
                    key  : 'content-length',
                    value: strlen((string)$message->getBody()),
                ),

            /*
             * @see 2.3. Signature Parameters @Important after Processing all other components
             */
            DerivedComponentEnum::signatureParams->value =>
                fn () => throw new UnexpectedValueException(
                    'remove before processing signatureParams',
                ),

        ];
    }
}
