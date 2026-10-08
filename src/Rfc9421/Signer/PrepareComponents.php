<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signer;

use BadMethodCallException;
use Closure;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use UnexpectedValueException;

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
        private(set) readonly RequestInterface $message,
        /**
         * otherwise use the default stack
         *
         * @var array{string:Closure}
         */
        private(set) ?array $methodStack = null,
    ) {
    }

    /**
     * @param array{string:Closure} $values
     */
    public function setMappingValues(array $values): self
    {
        foreach ($values as $componentName => $callable) {
            $this->setMappingValue(key: $componentName, callable: $callable);
        }

        return $this;
    }

    public function setMappingValue(string $key, Closure $callable): self
    {
        // init stack
        $stack             = $this->getMethodStack();
        $stack[$key]       = $callable;
        $this->methodStack = $stack;

        return $this;
    }

    /**
     * use default is nothing was overridden
     *
     * @return array{string:Closure}
     */
    private function getMethodStack(): array
    {
        return $this->methodStack ??= self::defaultMapperClosure();
    }

    /**
     * @return array{string:Closure}
     */
    public static function defaultMapperClosure(): array
    {
        /**
         * for different components there can be different methods to prepare the values(upper-case lower-case )
         *

         */
        return [
            /**
             * extracting the "method" from header to use later as "@method"
             *
             * @see 2.2.1. Method
             */
            '@method'         => fn(RequestInterface $message): string => strtoupper($message->getMethod()),
            '@target-uri'     => self::processTargetUriClosure(...),
            /**
             * @see 2.2.3. Authority
             */
            '@authority'      => fn(RequestInterface $message): string => $message->getUri()
                                                                          ->getHost(),
            /**
             * @see 2.2.4. Scheme
             */
            '@scheme'         => fn(RequestInterface $message): string => $message->getUri()
                /** * Todo More complex as described in 2.2.5. Request Target**/ ->getScheme(),
            '@request-target' => fn(MessageInterface $message) => throw new BadMethodCallException(
                '@request-target not implemented',
            ),
            /**
             * @see 2.2.6. Path
             **/
            '@path'           => self::processPathClosure(...),
            //  '@status'         => 'processStatus',
            'content-length'  => fn(RequestInterface $message): int => strlen((string)$message->getBody()),
        ];
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

    public function processValue(string $key): int|string
    {
        if (!array_key_exists($key, $this->getMethodStack())) {
            throw new UnexpectedValueException(sprintf('Method "%s" not registered.', $key));
        }

        return $this->getMethodStack()[$key]($this->message)
        ;
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
