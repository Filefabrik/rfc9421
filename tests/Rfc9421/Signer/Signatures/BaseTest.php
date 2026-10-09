<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Signer\Signatures;

use DateTime;
use Filefabrik\Rfc9421\Cryptography\SigningEnum;
use Filefabrik\Rfc9421\Exceptions\AlreadySetException;
use Filefabrik\Rfc9421\Exceptions\ContentDigestException;
use Filefabrik\Rfc9421\Exceptions\InvalidDerivedKeyException;
use Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException;
use Filefabrik\Rfc9421\Signer\Signatures\Base;
use Filefabrik\Rfc9421\Signer\Signatures\Line;
use Filefabrik\Rfc9421\Signer\Signatures\WriteLines;
use Filefabrik\Rfc9421\SigningConfiguration;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use UnexpectedValueException;

#[CoversClass(Base::class)]
final class BaseTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidDerivedKeyException
     */
    public function testCreateSignatureLines(): void
    {
        $sc = new SigningConfiguration();
        $sc->signHeader('not-ExistinG')
           ->signHeader('@method')
        ;
        $r = new Request('post', '', ['noT-exIsting' => 'hm']);
        $i = new Base($r, $sc);

        $arr = $i->createSignatureLines()
                 ->toArray()
        ;

        self::assertEquals([['not-existing', 'hm'], ['@method', 'POST']], $arr);
    }

    /**
     * @throws AlreadySetException
     * @throws ContentDigestException
     * @throws InvalidDerivedKeyException
     * @throws InvalidHeaderKeyException
     */
    public function testHandleSignBody(): void
    {
        $sc = new SigningConfiguration();
        $sc->signHeader('not-ExistinG')
           ->signHeader('@method')
           ->signHeader('content-digest')
           ->setContentDigestHashAlgo('sha256')
        ;
        $r     = new Request('post', '', ['noT-exIsting' => 'hm']);
        $i     = new Base($r, $sc);
        $lines = $i->createSignatureLines();
        self::assertEquals(
            new Line('not-existing', 'hm'),
            $lines->getLineNumber(0),
        );
        self::assertEquals(
            new Line('@method', 'POST'),
            $lines->getLineNumber(1),
        );
        self::assertEquals('content-digest', $lines->getLineNumber(2)->key);
    }

    /**
     * @throws AlreadySetException
     * @throws ContentDigestException
     * @throws InvalidDerivedKeyException
     * @throws InvalidHeaderKeyException
     */
    public function testHandleSignBodyThrowConfig(): void
    {
        $this->expectExceptionObject(
            new UnexpectedValueException(
                'Expecting content-digest from signingConfiguration header but mustSignBody is false',
            ),
        );
        $sc = new SigningConfiguration();
        $sc->signHeader('not-ExistinG')
           ->signHeader('@method')
            // do not use this
           ->signHeader('content-digest')
        ;
        $r = new Request('post', '', ['noT-exIsting' => 'hm']);
        $i = new Base($r, $sc);
        $i->createSignatureLines();
    }

    /**
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
        $i = new Base($r, $sc);
        $i->createSignatureLines();
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidDerivedKeyException
     */
    public function testMakeSignatureInput(): void
    {
        $sc      = new SigningConfiguration();
        $created = new DateTime()->getTimestamp();
        $sc->signHeader('stupid-header', '@method')
           ->withSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_256)
           ->withCreated($created)
           ->withSignatureKeyName('')
        ;
        $r   = new Request('POST', '', ['stupid-header' => 'hm']);
        $i   = new Base($r, $sc);
        $res = $i->makeSignatureInput();
        self::assertEquals(
            '("stupid-header" "@method");created='.$created.';alg="'.SigningEnum::rsa_v_1_5_sha_256->getValue().'"',
            $res,
        );
    }

    public function testCompileSignatureInputToString(): void
    {
        $sls = new WriteLines()->addEntry('header-key', 'the-value');

        $res = Base::compileSignatureInputToString($sls);

        self::assertEquals('"header-key": the-value', $res);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidDerivedKeyException
     */
    public function testWithPreparedComponents(): void
    {
        $sc = new SigningConfiguration();
        $sc->signHeader('not-ExistinG')
           ->signHeader('@method')
           ->signHeader('@authority')
        ;
        $sc->withPrepareComponentsMethod('@authority', fn(MessageInterface $message): string => 'game');
        $r = new Request('post', '', ['noT-exIsting' => 'hm']);
        $i = new Base($r, $sc);

        $arr = $i->createSignatureLines()
                 ->toArray()
        ;

        self::assertEquals([['not-existing', 'hm'], ['@method', 'POST'], ['@authority', 'game']], $arr);
    }

}
