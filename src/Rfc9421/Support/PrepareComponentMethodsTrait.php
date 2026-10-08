<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Support;

use Closure;
use Filefabrik\Rfc9421\SigningConfiguration;

/**
 * @codeCoverageIgnore
 */
trait PrepareComponentMethodsTrait
{
    /**
     * Injecting callables
     *
     * @var array{string, Closure}|array<void>
     */
    private(set) array $prepareComponentsMethods = [];

    /**
     * Modify the generation methods
     *
     * @param array{string,Closure} $methods
     *
     */
    public function withPrepareComponentsMethods(array $methods): SigningConfiguration
    {
        foreach ($methods as [$key, $closure]) {
            $this->withPrepareComponentsMethod($key, $closure);
        }

        return $this;
    }

    public function withPrepareComponentsMethod(string $componentName, callable $callable): self
    {
        $this->prepareComponentsMethods[$componentName] = $callable;

        return $this;
    }
}
