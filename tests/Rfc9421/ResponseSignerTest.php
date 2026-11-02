<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test;

use Filefabrik\Rfc9421\Cryptography\SigningEnum;
use Filefabrik\Rfc9421\Exceptions\AbstractRfc9421Exception;
use Filefabrik\Rfc9421\ResponseSigner;
use Filefabrik\Rfc9421\Signature\DerivedComponentEnum;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421Test\Tools\CertHelper;
use GuzzleHttp\Psr7\Response;
use JsonException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ResponseSigner::class)]
final class ResponseSignerTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        require_once dirname(__DIR__, 2).'/src/register-openssl-signing-verify.php';
    }

    /**
     * todo missing tests
     *
     * @throws \Bakame\Http\StructuredFields\StructuredFieldError
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     */
    public function testSign(): void
    {
        $certInstance       = new CertHelper('rsa');
        $responseBodyParams = ['account' => 'Alex', 'new-amount' => 900000000];
        try {
            $responseBodyJson = json_encode($responseBodyParams, JSON_THROW_ON_ERROR);
        } catch (JsonException $jsonException) {
            echo $jsonException->getMessage();

            return;
        }

        $response = new Response(headers: [], body: $responseBodyJson);

        //... now sign the response
        try {
            $responseSigningConfiguration = new SigningConfiguration()
                /*
                 * Content Length as a special method
                 *
                 * The content-length will be created to header by guzzle or something AFTER we are signing the request/response. So we have to calculate the length before.
                 * @important, when the receiver of this response verifies this response the `->processWithoutExistingHeaders(['content-length'])` never set.
                */
                //   ->processWithoutExistingHeaders(['content-length'])
                // make sure the receiver Alice expects the following response Key
                ->setSignatureKeyName('responseSig=')
                // singing the status as relevant information for the requester Alice, to ensure the status
                ->signHeader(DerivedComponentEnum::status)
                // todo header exists and must not be set separate as header
                ->signHeader('content-length')
                // the sender of the request expects an algo that Bob can check while receive this signed response
                ->setSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_512)
                // set alice private pem to encrypt the response
                ->setPrivatePem($certInstance->getPrivatPemContent())
                // singing the response body
                ->setContentDigestAlgo('sha256')
            ;
        } catch (AbstractRfc9421Exception $e) {
            echo 'response signing configuration error: '.$e->getMessage();

            return;
        }
        $responseSigner = new ResponseSigner(message: $response, signingConfiguration: $responseSigningConfiguration);

        $signedResponse = $responseSigner->sign();
        $signedHeaders  = $signedResponse->getHeaders();
        self::assertSame(
            'responseSig=("@status" "content-length" "content-digest");alg="rsa-v1-5-sha512"',
            $signedHeaders['signature-input'][0],
        );
        $headerKeys = array_keys($signedHeaders);
        self::assertContains('content-digest', $headerKeys);

        self::assertSame(
            'responseSig=("@status" "content-length" "content-digest");alg="rsa-v1-5-sha512"',
            $responseSigner->signatureBase,
        );
        self::assertSame(
            '"@status": 200
"content-length": 41
sha-256=:ZCEnsqX7hCc+COMsU1B7wNyKEdBqHR0ItAHl8HsEbus=:
responseSig=("@status" "content-length" "content-digest");alg="rsa-v1-5-sha512"',
            $responseSigner->signingParametersString,
        );
    }
}
