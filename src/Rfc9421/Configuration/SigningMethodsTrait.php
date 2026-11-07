<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Configuration;

use Closure;
use Filefabrik\Rfc9421\Enums\DerivedComponentEnum;
use Filefabrik\Rfc9421\Signer\PrepareComponents;
use Filefabrik\Rfc9421\Support\PreparedMethods;

/**
 * for signing methods
 */
trait SigningMethodsTrait
{
    private ?PreparedMethods $signingMethods = null;

    public function addSigningMethod(DerivedComponentEnum|string $componentName, Closure $closure): self
    {
        $this->getSigningMethodsWithDefaults()
             ->add($componentName, $closure)
        ;

        return $this;
    }

    public function getSigningMethodsWithDefaults(): PreparedMethods
    {
        return $this->signingMethods ??= PrepareComponents::defaultMapperClosure();
    }

    public function addSigningMethods(array $methods): self
    {
        $this->getSigningMethodsWithDefaults()
             ->adds($methods)
        ;

        return $this;
    }

}
