<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Signer\Signatures;

use DateTime;
use Filefabrik\Rfc9421\Enums\SigningEnum;
use Filefabrik\Rfc9421\Exceptions\ContentDigestException;
use Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException;
use Filefabrik\Rfc9421\Signer\Signatures\Base;
use Filefabrik\Rfc9421\Signer\Signatures\Line;
use Filefabrik\Rfc9421\Signer\Signatures\WriteLines;
use Filefabrik\Rfc9421\SigningConfiguration;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;

#[CoversClass(Base::class)]
final class BaseTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     */
    public function testCreateSignatureLines(): void
    {
        $sc = new SigningConfiguration();
        $sc->signHeader('not-ExistinG')
           ->signHeader('@method')
        ;
        $sc->getStructuredFields()
           ->add('not-existing', 'etag')
        ;
        $r = new Request('post', '', ['noT-exIsting' => 'hm']);
        $i = new Base($r, $sc);

        $arr = $i->createSignatureLines()
                 ->toKeyValueArray()
        ;

        self::assertEquals(['"not-existing"' => '"not-existing": hm', '"@method"' => '"@method": POST'], $arr);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     */
    public function testHandleSignBody(): void
    {
        $sc = new SigningConfiguration();
        $sc->signHeader('not-ExistinG')
           ->signHeader('@method')
           ->signHeader('content-digest')
           ->setContentDigestAlgo('sha256')
        ;
        $r     = new Request('post', '', ['noT-exIsting' => 'hm']);
        $i     = new Base($r, $sc);
        $lines = $i->createSignatureLines();
        self::assertEquals(
            new Line('"not-existing"', '"not-existing": hm'),
            $lines->getLines()[0],
        );
        self::assertEquals(
            new Line('"@method"', '"@method": POST'),
            $lines->getLines()[1],
        );
        self::assertEquals('content-digest', $lines->getLines()[2]->signatureComponent);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     */
    public function testHandleSignBodyThrowConfig(): void
    {
        $this->expectExceptionObject(
            new ContentDigestException(
                'content-digest header is set but the ContentDigestHashAlgo was not configuration.',
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
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
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
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testMakeSignatureInput(): void
    {
        $sc      = new SigningConfiguration();
        $created = new DateTime()->getTimestamp();
        $sc->signHeaders(['stupid-header', '@method'])
           ->setSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_256)
           ->setCreated($created)
           ->setSignatureKeyName('')
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
        $sls = new WriteLines()->addEntry('header-key', '"header-key": the-value');

        $res = Base::compileSignatureInputToString($sls);

        self::assertEquals('"header-key": the-value', $res);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     */
    public function testWithPreparedComponents(): void
    {
        $sc = new SigningConfiguration();
        $sc->signHeader('not-ExistinG')
           ->signHeader('@method')
           ->signHeader('@authority')
        ;

        $sc->getStructuredFields()
           ->add('not-existing', 'etag')
        ;
        $sc->addSigningMethod(componentName: '@authority', closure: fn (MessageInterface $message): string => 'game');
        $r = new Request('post', '', ['noT-exIsting' => 'hm']);
        $i = new Base($r, $sc);

        $arr          = $i->createSignatureLines()->getLines();
        $expectations = [
            ['"not-existing"', '"not-existing": hm'],
            ['"@method"', '"@method": POST'],
            ['"@authority"', '"@authority": game'],
        ];
        foreach ($expectations as $cnt => $item) {
            $line = $arr[$cnt];
            self::assertSame($item[0], $line->signatureComponent);
            self::assertSame($item[1], $line->valueToSign);
        }
    }

}
