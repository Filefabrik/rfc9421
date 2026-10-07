<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Signer;

use BadMethodCallException;
use Closure;
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
      };
 */
/**
 * todo closure processing
 * @phpstan-type defaultStack array{'@method': 'processAtMethod', '@target-uri': 'processTargetUri', '@authority': 'processAuthority', '@scheme': 'processScheme', '@request-target': 'processHost', '@path': 'processPath', content-length: 'processContentLength'}
 * @phpstan-type entry array{string: Closure|string}
 */
final class PrepareComponents
{
    /**
     * for different components there can be different methods to prepare the values(upper-case lower-case )
     *
     * @phpstan-var defaultStack
     */
    private static array $defaultDerivedStack = [
        '@method'         => 'processAtMethod',
        '@target-uri'     => 'processTargetUri',
        '@authority'      => 'processAuthority',
        '@scheme'         => 'processScheme',
        '@request-target' => 'processHost',
        '@path'           => 'processPath',
        //  '@status'         => 'processStatus',
        'content-length'  => 'processContentLength',
    ];

    public function __construct(
        public private(set) readonly RequestInterface $message,
        /**
         * otherwise use the default stack
         *
         * @var array{string:string|Closure}
         */
        public private(set) ?array $derivedMapper = null,
    ) {
    }

    /**
     * @return defaultStack
     */
    public static function getDefaultDerivedStack(): array
    {
        return self::$defaultDerivedStack;
    }

    /**
     * Todo setter has to be a type/format validator that is callable or closure
     * While having systemwide an own mapping dialect for your needs, set the method stack globally
     *
     * @param entry|defaultStack $defaultDerivedStack
     */
    public static function setDefaultDerivedStack(array $defaultDerivedStack): void
    {
        self::$defaultDerivedStack = $defaultDerivedStack;
    }

    /**
     * Configure later the MethodStack if need
     *
     * @param array{string:Closure|string}|null $derivedMapper
     */
    public function setDerivedMapper(?array $derivedMapper): self
    {
        $this->derivedMapper = $derivedMapper;

        return $this;
    }

    /**
     * @param array{string:string|Closure} $values
     */
    public function setMappingValues(array $values): self
    {
        foreach ($values as $componentName => $callable) {
            $this->setMappingValue(key: $componentName, callable: $callable);
        }

        return $this;
    }

    public function setMappingValue(string $key, Closure|string $callable): self
    {
        // init stack
        $stack               = $this->getMethodStack();
        $stack[$key]         = $callable;
        $this->derivedMapper = $stack;

        return $this;
    }

    /**
     * @return array{string:Closure|string}|defaultStack
     */
    private function getMethodStack(): array
    {
        return $this->derivedMapper ??= self::$defaultDerivedStack;
    }

    public function processValue(string $key): int|string
    {
        if (!array_key_exists($key, $this->getMethodStack())) {
            throw new UnexpectedValueException(sprintf('Method "%s" not registered.', $key));
        }

        $callableMethod = $this->getMethodStack()[$key];

        if ($callableMethod instanceof Closure) {
            return $callableMethod($this->message);
        }

        if (method_exists($this, $callableMethod)) {
            return $this->$callableMethod();
        }

        throw new BadMethodCallException(sprintf('Method "%s" not callable.', $callableMethod));
    }

    /**
     * extracting the "method" from header to use later as "@method"
     *
     * @see 2.2.1. Method
     */
    public function processAtMethod(): string
    {
        return strtoupper($this->message->getMethod());
    }

    /**
     * todo,needs more testing
     *
     * @see 2.2.2. Target URI @target-uri
     */
    public function processTargetUri(): string
    {
        $messageUri = clone $this->message->getUri();
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
     * @see 2.2.3. Authority
     */
    public function processAuthority(): string
    {
        return $this->message->getUri()
                             ->getHost()
        ;
    }

    /**
     * @see 2.2.4. Scheme
     */
    public function processScheme(): string
    {
        return $this->message->getUri()
                             ->getScheme()
        ;
    }

    /*
     * Todo More complex as described in 2.2.5. Request Target
     *
     * @return void
     *
     *
     * public function processRequestTarget()
     * {
     * }*/

    /**
     * @return string empty with /
     * @see 2.2.6. Path
     */
    public function processPath(): string
    {
        $p = $this->message->getUri()
                           ->getPath()
        ;

        return (!str_starts_with($p, '/')) ? '/'.$p : $p;
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
    public function processContentLength(): int
    {
        return strlen((string)$this->message->getBody());
    }
}
