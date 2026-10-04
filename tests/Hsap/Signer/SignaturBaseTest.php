<?php

declare(strict_types=1);

namespace Filefabrik\HsapTest\Signer;

use DateTime;
use Filefabrik\Hsap\Cryptography\SigningEnum;
use Filefabrik\Hsap\Exceptions\AlreadySetException;
use Filefabrik\Hsap\Exceptions\ContentDigestException;
use Filefabrik\Hsap\Exceptions\InvalidDerivedKeyException;
use Filefabrik\Hsap\Exceptions\InvalidHeaderKeyException;
use Filefabrik\Hsap\Signer\SignaturBase;
use Filefabrik\Hsap\SigningConfiguration;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Filefabrik\Hsap\Signer\SignaturBase::class)]
final class SignaturBaseTest extends TestCase
{
    public function testCreateSignatureLines(): void
    {
        $sc = new SigningConfiguration();
        $sc->signHeader('not-ExistinG')
           ->signHeader('@method')
        ;
        $r = new Request('post', '', ['noT-exIsting' => 'hm']);
        $i = new SignaturBase($r, $sc);
        $this->assertEquals([['not-existing', 'hm'], ['@method', 'POST']], $i->createSignatureLines());
    }

    /**
     * @covers \Filefabrik\Hsap\Signer\SignaturBase::createSignatureLine
     * @return void
     * @throws AlreadySetException
     * @throws ContentDigestException
     * @throws InvalidDerivedKeyException
     * @throws InvalidHeaderKeyException
     */
    public function testCreateSignatureLinesThrowMissingHeader(): void
    {
        $this->expectExceptionObject(
            new InvalidHeaderKeyException(
                'The header "not-existing" does not exist in the message. Note:headers can only be a part of the signing process if they are existing. $message->withHeader("not-existing", \'your value\')',
            ),
        );

        $sc = new SigningConfiguration();
        $sc->signHeader('not-existing');
        $r = new Request('POST', '', ['x-wrong-name' => 'hm']);
        $i = new SignaturBase($r, $sc);
        $i->createSignatureLines();
    }

    public function testMakeSignatureInput(): void
    {
        $sc      = new SigningConfiguration();
        $created = new DateTime()->getTimestamp();
        $sc->signHeader('stupid-header', '@method')
           ->withSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_256)
           ->withCreated($created)->withSignatureKeyName('')
        ;
        $r   = new Request('POST', '', ['stupid-header' => 'hm']);
        $i   = new SignaturBase($r, $sc);
        $res = $i->makeSignatureInput();
        $this->assertEquals(
            '("stupid-header" "@method");created='.$created.';alg="'.SigningEnum::rsa_v_1_5_sha_256->getValue().'"',
            $res,
        );
    }

    public function testCompileSignatureInputToString(): void
    {
        $this->expectNotToPerformAssertions();
    }

}
