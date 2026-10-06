<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Support;

use Filefabrik\Rfc9421\SigningConfiguration;

/**
 * @codeCoverageIgnore
 */
trait PrepareComponentMethodsTrait
{
    /**
     * Injecting callables
     *
     * @var array
     */
    public private(set) array $prepareComponentsMethods = [];

    /**
     * Modify the generation methods
     *
     * @param array $methods
     *
     * @return SigningConfiguration
     * @see
     */
    public function withPrepareComponentsMethods(array $methods): SigningConfiguration
    {
        foreach ($methods as $keyValueArray) {
            $this->withPrepareComponentsMethod(...$keyValueArray);
        }

        return $this;
    }

    public function withPrepareComponentsMethod($componentName, $callable): self
    {
        $this->prepareComponentsMethods[$componentName] = $callable;

        return $this;
    }
}
