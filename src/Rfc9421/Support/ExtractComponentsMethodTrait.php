<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Support;

use Closure;
use Filefabrik\Rfc9421\Signature\DerivedComponentEnum;

/**
 * Extraction Mechanism incoming request
 *
 * @codeCoverageIgnore
 */
trait ExtractComponentsMethodTrait
{
    /**
     * for extracting
     */
    private(set) ?PreparedMethods $extractComponentMethods = null {
        get => $this->extractComponentMethods ??= new PreparedMethods();
    }

    public function addExtractMethod(DerivedComponentEnum|string $componentName, Closure $closure): self
    {
        $this->extractComponentMethods->add($componentName, $closure);

        return $this;
    }

    public function addExtractMethods(array $methods): self
    {
        $this->extractComponentMethods->adds($methods);

        return $this;
    }

}
