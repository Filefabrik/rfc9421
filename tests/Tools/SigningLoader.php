<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Tools;

use Filefabrik\Rfc9421\Cryptography\SignatureRegistry;
use Filefabrik\Rfc9421\Cryptography\VerifyRegistry;

final class SigningLoader
{
    public static function ensureHasValues()
    {
        SignatureRegistry::clearRegistry();
        VerifyRegistry::clearRegistry();
        require dirname(__DIR__, 2).'/src/register-openssl-signing-verify.php';
    }
}
