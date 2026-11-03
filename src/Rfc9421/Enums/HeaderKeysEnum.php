<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421\Enums;

/**
 * Static Values to prevent from typo errors
 */
enum HeaderKeysEnum: string
{
    case content_digest  = 'content-digest';
    case signature_input = 'signature-input';

    case signature = 'signature';
}
