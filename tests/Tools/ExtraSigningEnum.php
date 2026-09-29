<?php

declare(strict_types=1);

namespace Filefabrik\HsapTest\Tools;

use Filefabrik\Hsap\Cryptography\SigningTrait;
use Filefabrik\Hsap\Interfaces\SigningEnumInterface;

/**
 * Testing extra Signing Enum Class
 */
enum ExtraSigningEnum: string implements SigningEnumInterface
{
    use SigningTrait;

    case testing_rsa_pss_sha512 = 'testing_rsa-pss-sha512';
}
