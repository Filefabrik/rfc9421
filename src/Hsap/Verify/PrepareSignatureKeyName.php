<?php

declare(strict_types=1);

namespace Filefabrik\Hsap\Verify;

use Exception;
use Filefabrik\Hsap\Exceptions\VerifyHeaderSignatureException;
use Filefabrik\Hsap\Extractors\HeaderSignature;
use Filefabrik\Hsap\SigningConfiguration;

final class PrepareSignatureKeyName
{
    public function __construct(
        public private(set) SigningConfiguration $signingConfiguration,
        public private(set) ?HeaderSignature $headerSignatureExtractor = null,
    ) {
    }
    /**
     * Handling the signature key extraction process
     *
     * @return void
     * @throws VerifyHeaderSignatureException
     */
    public function prepare(): void
    {
        if (!$this->signingConfiguration->signatureKeyName) {
            try {
                //* Extracts the "sig1=" from the 'sig1=("@method" "content-digest");created=1789634264;tag="mastodon"';
                // todo works if only one signature-input and only one signature
                // todo check more then one
                $signatureKeyName = $this->headerSignatureExtractor->extractSignatureNameKey();
            } catch (Exception) {
                throw VerifyHeaderSignatureException::keynameNotExtracted();
            }
            /**
             * extracted signature key from the "signature" which is the key for extracting the "components" and the "metadata"
             * todo maybe decide we are using an signature key name if there are more then one signatures
             * */
            $this->signingConfiguration
                ->withSignatureKeyName(signatureKey: $signatureKeyName)
            ;
        }
    }
}
