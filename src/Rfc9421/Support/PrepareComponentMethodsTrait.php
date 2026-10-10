<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Support;

use Closure;
use Filefabrik\Rfc9421\Signer\PrepareComponents;
use Filefabrik\Rfc9421\SigningConfiguration;

/**
 * @codeCoverageIgnore
 */
trait PrepareComponentMethodsTrait
{
    private(set) ?PreparedMethods $preparedMethods = null;

    /**
     * Modify the generation methods
     *
     * @param array{array{string:Closure}} $methods
     *
     */
    public function withPrepareComponentsMethods(array $methods): SigningConfiguration
    {
        $this->getPreparedMethods()
             ->adds($methods)
        ;

        return $this;
    }

    public function getPreparedMethods(): PreparedMethods
    {
        return $this->preparedMethods ??= PrepareComponents::defaultMapperClosure();
    }

    public function withPrepareComponentsMethod(string $componentName, Closure $callable): self
    {
        $this->getPreparedMethods()
             ->add($componentName, $callable)
        ;

        return $this;
    }

}
