<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Support;

use Closure;
use Filefabrik\Rfc9421\Signature\DerivedComponentEnum;
use Filefabrik\Rfc9421\Signer\PrepareComponents;

/**
 * @codeCoverageIgnore
 */
trait SigningMethodsTrait
{
    /**
     * for signing methods
     */
    private(set) ?PreparedMethods $signingMethods = null {
        get => $this->signingMethods ??= PrepareComponents::defaultMapperClosure();
    }

    public function addSigningMethod(DerivedComponentEnum|string $componentName, Closure $closure): self
    {
        $this->signingMethods->add($componentName, $closure);

        return $this;
    }

    public function addSigningMethods(array $methods): self
    {
        $this->signingMethods->adds($methods);

        return $this;
    }

}
