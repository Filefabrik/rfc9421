<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signer;

use BadMethodCallException;
use Closure;
use Filefabrik\Rfc9421\Signature\DerivedComponentEnum;
use Filefabrik\Rfc9421\Support\PreparedMethods;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Specific Request Headers has to be prepared
 */
/*
 * foreach ($componentNames as $name) {
      $components[$name] = match ($name) {
        // 2.2.1. Note that the method name is case-sensitive as per HTTP,
        // Section 9.1. While conventionally standardized names are uppercase,
        // no transformation to the input method value's case is performed.
        '@method' => '',
        // The URL should be considered case-sensitive due to operating system
        // issues and query string percent-encoded octets.
        '@target-uri' => $fullSchemeHostPathQueryString,
        // Normalized to lowercase and the default port is omitted. This could
        // also be in X-Original-Host rather than Host header for verification.
        '@authority' => strtolower($host),
        // The scheme MUST be normalized to lowercase.
        '@scheme' => strtolower($scheme),
        // The request target is path AND the query string, if provided.
        '@request-target' => $requestTarget,
        // The path is only the path, and not the query string.
        '@path' => $path,
        // The raw query string or empty. The simplest thing. Use this
        // instead of @query-param, please.
        '@query' => $queryString,
        // Section 2.2.8, the dumbest signature component that nobody should
        // fucking use ever. Please don’t do this. It is too annoying to
        // even think about.
        '@query-param' => $parsedAndEncodedQueryParameters,
        // Default is an HTTP field name.
        default => $headerValueCorrespondingToHeaderName,
        }
      };
 */
/**
 * todo closure processing
 * @phpstan-type entry array{string: Closure|string}
 */
final class PrepareComponents
{
    public function __construct(
        private(set) readonly RequestInterface|ResponseInterface $message,
        private(set) ?PreparedMethods $preparedMethods = null,
    ) {
    }

    /**
     * todo,needs more testing
     *
     * @see 2.2.2. Target URI @target-uri
     */
    public static function processTargetUriClosure(RequestInterface $message): string
    {
        $messageUri = clone $message->getUri();
        $parts      = [];
        $parts[]    = $messageUri->getHost();
        if ('' !== ($path = $messageUri->getPath())) {
            $parts[] = '/'.ltrim($path, '/');
        }

        if ('' !== ($query = $messageUri->getQuery())) {
            $parts[] = '?'.ltrim($query, '?');
        }

        return implode('', $parts);
    }

    /**
     * @return string empty with /
     * @see 2.2.6. Path
     */
    public static function processPathClosure(RequestInterface $message): string
    {
        $p = $message->getUri()
                     ->getPath()
        ;

        return (!str_starts_with($p, '/')) ? '/'.$p : $p;
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
                closure: fn (RequestInterface $message): string => strtoupper(
                    $message->getMethod(),
                ),
            )
            ->add(
                key    : DerivedComponentEnum::targetUri,
                closure: self::processTargetUriClosure(...),
            )
            /*
             * @see 2.2.3. Authority
             */
            ->add(
                key    : DerivedComponentEnum::authority,
                closure: fn (RequestInterface $message): string => $message->getUri()
                                                                          ->getHost(),
            )
            /*
             * @see 2.2.4. Scheme
             */
            ->add(
                key    : DerivedComponentEnum::scheme,
                closure: fn (RequestInterface $message): string => $message->getUri()
                                                                          ->getScheme(),
            )
            /*
             * 2.2.5. Request Target
             * Todo More complex as described in 2.2.5. Request Target
             */
            ->add(
                key    : DerivedComponentEnum::requestTarget,
                closure: fn (RequestInterface $message) => throw new BadMethodCallException(
                    '@request-target not implemented',
                ),
            )
            /*
             * @see 2.2.6. Path
             */
            ->add(
                key    : DerivedComponentEnum::path,
                closure: self::processPathClosure(...),
            )
            ->add(
                key    : 'content-length',
                closure: fn (RequestInterface|ResponseInterface $message): int => strlen(
                    (string)$message->getBody(),
                ),
            )
            ->add(
                key    : DerivedComponentEnum::status,
                closure: fn (ResponseInterface $message): int => $message->getStatusCode(),
            )
        ;
    }

    public function setPreparedMethods(PreparedMethods $preparedMethods): self
    {
        $this->preparedMethods = $preparedMethods;

        return $this;
    }

    public function processValue(string $key): int|string
    {
        return $this->getMethodStack()
                    ->getMethod($key)(
                        $this->message,
                    );
    }

    /*
     * todo query 2.2.7. Query as it is more komplex
     * todo 2.2.8. Query Parameters as it is more complex
     */
    /*
         * todo, the status can only be in the response, have a look what todo
         *
         * @return int

        public function processStatusCode(): int
        {
        }   */
    // todo 2.3. Signature Parameters über signature params preconfigured

}
