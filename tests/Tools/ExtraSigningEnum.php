<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Tools;

use Filefabrik\Rfc9421\Cryptography\SigningTrait;
use Filefabrik\Rfc9421\Interfaces\SigningEnumInterface;

/**
 * Testing extra Signing Enum Class
 */
enum ExtraSigningEnum: string implements SigningEnumInterface
{
    use SigningTrait;

    case testing_rsa_pss_sha512 = 'testing_rsa-pss-sha512';
}
