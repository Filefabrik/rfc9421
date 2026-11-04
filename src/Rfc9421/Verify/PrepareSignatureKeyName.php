<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * todo insert correct copyright
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421\Verify;

use Exception;
use Filefabrik\Rfc9421\Exceptions\VerifyHeaderSignatureException;
use Filefabrik\Rfc9421\Extractors\HeaderSignature;
use Filefabrik\Rfc9421\SigningConfiguration;

/**
 * extracts from the ['signature'=>']
 */
final readonly class PrepareSignatureKeyName
{
    public function __construct(
        private(set) SigningConfiguration $signingConfiguration,
        private(set) HeaderSignature $headerSignatureExtractor,
    ) {
    }

    /**
     * Handling the signature key validatedSignatureParts process
     *
     * @throws VerifyHeaderSignatureException
     */
    public function prepare(): void
    {
        if (null === $this->signingConfiguration->signatureKeyName) {
            try {
                //* Extracts the "sig1=" from the 'sig1=("@method" "content-digest");created=1789634264;tag="mastodon"';
                // todo works if only one signature-input and only one signature
                // todo check more then one
                $signatureKeyName = $this->headerSignatureExtractor->extractSignatureNameKey();
                // @codeCoverageIgnoreStart
            } catch (Exception) {
                throw VerifyHeaderSignatureException::keynameNotExtracted();
                // @codeCoverageIgnoreEnd
            }

            /*
             * extracted signature key from the "signature" which is the key for extracting the "components" and the "metadata"
             * todo maybe decide we are using an signature key name if there are more then one signatures
             * */
            $this->signingConfiguration
                ->setSignatureKeyName(signatureKey: $signatureKeyName)
            ;
        }
    }
}
