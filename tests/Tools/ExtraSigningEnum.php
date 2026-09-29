<?php

declare(strict_types=1);

namespace Filefabrik\HsapTest\Tools;

use Filefabrik\Hsap\Interfaces\SigningEnumInterface;
use Filefabrik\Hsap\Support\EnumSupportTrait;

/**
 * Testing extra Signing Enum Class
 */
enum ExtraSigningEnum: string implements SigningEnumInterface
{
    use EnumSupportTrait;

    case testing_rsa_pss_sha512 = 'testing_rsa-pss-sha512';
}
