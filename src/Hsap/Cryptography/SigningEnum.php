<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Cryptography;

use Filefabrik\Hsap\Interfaces\SigningEnumInterface as SigningEnumInterfaceAlias;
use Filefabrik\Hsap\Support\EnumSupportTrait;

/**
 * keys are snake cased, value as in @see https://www.rfc-editor.org/rfc/rfc9421.html
 */
enum SigningEnum: string implements SigningEnumInterfaceAlias
{
    use EnumSupportTrait;

    /**
     * 3.3.1. RSASSA-PSS Using SHA-512
     * rsa-pss-sha512
     */
    case rsa_pss_sha512 = 'rsa-pss-sha512';
    /**
     * 3.3.2. RSASSA-PKCS1-v1_5 Using SHA-256
     * rsa-v1_5-sha256
     *
     * @see https://www.rfc-editor.org/rfc/rfc9421.html#name-rsassa-pkcs1-v1_5-using-sha
     *
     * Mastodon
     * @see https://docs.joinmastodon.org/spec/security/#http-message-signatures
     */
    case rsa_v_1_5_sha_256 = 'rsa-v1_5-sha256';
    /* not in rfc */
    case rsa_v_1_5_sha_384 = 'rsa-v1-5-sha384';
    /* not in rfc */
    case rsa_v_1_5_sha_512 = 'rsa-v1-5-sha512';
    /**
     * 3.3.3. HMAC Using SHA-256
     * hmac-sha256
     */
    case hmac_sha_256 = 'hmac-sha256';
    /* not in rfc */
    case hmac_sha_384 = 'hmac-sha384';
    /* not in rfc */
    case hmac_sha_512 = 'hmac-sha512';
    /**
     * 3.3.4. ECDSA Using Curve P-256 DSS and SHA-256
     * ecdsa-p256-sha256
     */
    case ecdsa_p_256_sha_256 = 'ecdsa-p256-sha256';
    /**
     * 3.3.5. ECDSA Using Curve P-384 DSS and SHA-384
     * ecdsa-p384-sha384
     */
    case ecdsa_p_384_sha_384 = 'ecdsa-p384-sha384';

    /* not in rfc */
    case ecdsa_p_512_sha_512 = 'ecdsa-p512-sha512';
    /**
     * 3.3.6. EdDSA Using Curve edwards25519
     * ed25519
     */
    case ed25519 = 'ed25519';

}
