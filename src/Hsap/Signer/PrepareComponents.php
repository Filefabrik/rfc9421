<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Signer;

use BadMethodCallException;
use Closure;
use Filefabrik\Hsap\SignatureParams;
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
        // Default is a HTTP field name.
        default => $headerValueCorrespondingToHeaderName,
      };
 */
final class PrepareComponents
{
    /**
     * for different components there can be different methods to prepare the values(upper-case lower-case )
     *
     * @var array<Closure|callable|string>
     */
    private static array $defaultMethodsStack = ['@method' => 'processAtMethod'];

    public function __construct(
        public private(set) readonly RequestInterface $message,
        public private(set) readonly SignatureParams $signatureParams,
        /**
         * otherwise use the default stack
         *
         * @var array<Closure|callable|string>
         */
        public private(set) ?array $methodStack = null,
    ) {
    }

    public static function getDefaultMethodsStack(): array
    {
        return self::$defaultMethodsStack;
    }

    /**
     * Todo setter has to be a type/format validator that is callable or closure
     * While having systemwide an own mapping dialect for your needs, set the method stack globally
     *
     * @param array $defaultMethodsStack
     *
     * @return void
     */
    public static function setDefaultMethodsStack(array $defaultMethodsStack): void
    {
        self::$defaultMethodsStack = $defaultMethodsStack;
    }

    /**
     * Configure later the MethodStack if need
     *
     * @param array<Closure|callable|string>|null $methodStack
     *
     * @return $this
     */
    public function setMethodStack(?array $methodStack): self
    {
        $this->methodStack = $methodStack;

        return $this;
    }

    public function processValue($key): int|string|float
    {
        $callableMethod = $this->getMethodStack()[$key] ??
            throw new UnexpectedValueException(sprintf('Method "%s" not registered.', $key));

        if ($callableMethod instanceof Closure) {
            return $callableMethod($this->message);
        }
        if (is_callable($callableMethod) || (is_string($callableMethod) && method_exists($this, $callableMethod))) {
            return $this->$callableMethod();
        }

        throw new BadMethodCallException(sprintf('Method "%s" not callable.', $callableMethod));
    }

    /**
     * @return array<Closure|callable|string>
     */
    private function getMethodStack(): array
    {
        return $this->methodStack ??= self::$defaultMethodsStack;
    }

    public function processAtMethod(): string
    {
        return strtoupper($this->message->getMethod());
    }
}
