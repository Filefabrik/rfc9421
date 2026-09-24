<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Support;

use Filefabrik\Hsap\SigningConfiguration;

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
