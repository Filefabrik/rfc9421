<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Signer\Signatures;

use DateTime;
use Filefabrik\Rfc9421\Enums\SigningEnum;
use Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException;
use Filefabrik\Rfc9421\Exceptions\UnexpectedValue;
use Filefabrik\Rfc9421\Signature\ComponentBuilder;
use Filefabrik\Rfc9421\Signer\Signatures\Base;
use Filefabrik\Rfc9421\Signer\Signatures\Line;
use Filefabrik\Rfc9421\Signer\Signatures\ReadLines;
use Filefabrik\Rfc9421\Signer\Signatures\WriteLines;
use Filefabrik\Rfc9421\SigningComponents;
use Filefabrik\Rfc9421\SigningConfiguration;
use GuzzleHttp\Psr7\Request;
use Nyholm\Psr7\Response;
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
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testCreateSignatureLines(): void
    {
        $sc     = new SigningComponents()->sign('not-ExistinG')
                                         ->sign('@method')
        ;
        $config = new SigningConfiguration()->setSigningComponents($sc);
        $config->getStructuredFields()
               ->add('not-existing', 'etag')
        ;
        $r = new Request('post', '', ['noT-exIsting' => 'hm']);
        $i = new Base($r, $config);

        $arr = $i->createSignatureLines()
                 ->toLineString()
        ;

        self::assertEquals(['"not-existing"' => '"not-existing": hm', '"@method"' => '"@method": POST'], $arr);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testCreateSignatureLinesResponseInterfaceWithReqValid(): void
    {
        $sc = new SigningConfiguration(
            signingComponents: new SigningComponents()
                ->signHeader(
                    ComponentBuilder::from('@method')
                                    ->withReq(),
                )
                ->sign('not-ExistinG'),
        );

        $sc->getStructuredFields()
           ->add('not-existing', 'etag')
        ;
        // simulate a request
        $writeLines = new WriteLines();

        // pure setter without ;asReq flag
        $writeLines->add(
            new Line(
                signatureComponent: '"@method"',
                valueToSign       : 'POST',
            ),
        );
        $rl = new ReadLines($writeLines);

        $r = new Response(headers: ['noT-exIsting' => 'hm']);

        $i = new Base(message: $r, configuration: $sc, readLines: $rl);
        $i->createSignatureLines();

        $arr = $i->createSignatureLines()
                 ->toLineString()
        ;

        self::assertEquals(
            [
                '"@method";req'  => '"@method";req: POST',
                '"not-existing"' => '"not-existing": hm',

            ],
            $arr,
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testCreateSignatureLinesResponseInterfaceWithReqThrows(): void
    {
        $this->expectExceptionObject(new UnexpectedValue('Request Interface can not have an ;req flag.'));

        $sc = new SigningConfiguration(
            signingComponents: new SigningComponents()
                ->signHeader(
                    ComponentBuilder::from('@method')
                                    ->withReq(),
                )
                ->sign('not-ExistinG'),
        );

        $sc->getStructuredFields()
           ->add('not-existing', 'etag')
        ;
        // simulate a request
        $writeLines = new WriteLines();

        // pure setter without ;asReq flag
        $writeLines->add(
            new Line(
                signatureComponent: '"@method"',
                valueToSign       : '"@method": POST',
            ),
        );
        $rl = new ReadLines($writeLines);

        $r = new Request('post', '', ['noT-exIsting' => 'hm']);

        $i = new Base(message: $r, configuration: $sc, readLines: $rl);
        $i->createSignatureLines();

        $arr = $i->createSignatureLines()
                 ->toKeyValueArray()
        ;

        self::assertEquals(
            [
                '"@method";req'  => '"@method";req: POST',
                '"not-existing"' => '"not-existing": hm',

            ],
            $arr,
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testCreateSignatureLinesResponseInterfaceThrows(): void
    {
        $this->expectExceptionObject(
            new UnexpectedValue('For Response signing the signed Request must be set into the Signer!'),
        );
        $sc = new SigningConfiguration(
            signingComponents: new SigningComponents()->sign('@method', true),
        );

        $response = new Response();
        $i        = new Base($response, $sc);
        $i->createSignatureLines();
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testCreateSignatureLinesResponseInterfaceThrowsNotFound(): void
    {
        $this->expectExceptionObject(
            new UnexpectedValue('Can not find "@method";req in the signed request "@method".'),
        );
        $sc = new SigningConfiguration(
            signingComponents: new SigningComponents()->sign('@method', true),
        );

        $response = new Response();
        $i        = new Base(message: $response, configuration: $sc, readLines: new ReadLines(new WriteLines()));
        $i->createSignatureLines();
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testCreateSignatureLinesWrongInterfaceReqThrows(): void
    {
        $this->expectExceptionObject(
            new UnexpectedValue('For Response signing the signed Request must be set into the Signer!'),
        );
        $sc = new SigningConfiguration(
            signingComponents: new SigningComponents()->sign('@method', true),
        );

        $response = new Response();
        $i        = new Base($response, $sc);
        $i->createSignatureLines();
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testHandleSignBody(): void
    {
        // Note: Ugly content-digest test
        $sc = new SigningConfiguration(
            signingComponents: new SigningComponents()->sign('not-ExistinG')
                                                      ->sign('@method')
                                                      ->sign('content-digest'),
        );
        $sc->withContentDigest('sha256');

        $r     = new Request('post', '', ['noT-exIsting' => 'hm', 'content-digest' => 'an value']);
        $i     = new Base($r, $sc);
        $lines = $i->createSignatureLines();
        self::assertEquals(
            new Line('"not-existing"', 'hm'),
            $lines->getLines()[0],
        );
        self::assertEquals(
            new Line('"@method"', 'POST'),
            $lines->getLines()[1],
        );
        self::assertEquals('"content-digest"', $lines->getLines()[2]->signatureComponent);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testHandleSignBodyThrowConfig(): void
    {
        $this->expectExceptionObject(
            new InvalidHeaderKeyException(
                'The header "content-digest" does not exist in the message. Note:headers can only be a part of the signing process if they are existing. $message->withHeader("content-digest", \'your value\')',
            ),
        );
        $sc = new SigningConfiguration(
            signingComponents: new SigningComponents()->sign('not-ExistinG')
                                                      ->sign('@method')
                // do not use this
                                                      ->signHeader('content-digest'),
        );
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
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testCreateSignatureLinesThrowMissingHeader(): void
    {
        $this->expectExceptionObject(
            new InvalidHeaderKeyException(
                'The header "not-existing" does not exist in the message. Note:headers can only be a part of the signing process if they are existing. $message->withHeader("not-existing", \'your value\')',
            ),
        );

        $sc = new SigningConfiguration(
            signingComponents: new SigningComponents()->sign('not-ExistinG'),
        );

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
        $sc      = new SigningConfiguration(
            signingComponents: new SigningComponents()->signHeaders(
                ['stupid-header', '@method'],
            ),
        );
        $created = new DateTime()->getTimestamp();
        $sc->setSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_256)
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

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testMakeSignatureInputWithKey(): void
    {
        $sc = new SigningConfiguration(
            signingComponents: new SigningComponents()->signHeaders(
                ['stupid-header', '@method'],
            ),
        );

        $created = new DateTime()->getTimestamp();
        $sc->setSigningAlgorithm(SigningEnum::rsa_v_1_5_sha_256)
           ->setCreated($created)
           ->setSignatureKeyName('playground=')
        ;
        $r   = new Request('POST', '', ['stupid-header' => 'hm']);
        $i   = new Base($r, $sc);
        $res = $i->makeSignatureInput();
        self::assertEquals(
            'playground=("stupid-header" "@method");created='.$created.';alg="'.SigningEnum::rsa_v_1_5_sha_256->getValue().'"',
            $res,
        );
    }

    public function testCompileSignatureInputToString(): void
    {
        $sls = new WriteLines()->addEntry('"header-key"', 'the-value');

        $res = Base::compileSignatureInputToString($sls);

        self::assertEquals('"header-key": the-value', $res);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\ContentDigestException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testWithPreparedComponents(): void
    {
        $sc = new SigningConfiguration(
            signingComponents: new SigningComponents()->sign('not-ExistinG')
                                                      ->sign('@method')
                                                      ->sign('@authority'),
        );

        $sc->getStructuredFields()
           ->add('not-existing', 'etag')
        ;
        $sc->addSigningMethod(
            componentName: '@authority',
            closure      : fn (MessageInterface $message): string => 'game',
        );
        $r = new Request('post', '', ['noT-exIsting' => 'hm']);
        $i = new Base($r, $sc);

        $arr          = $i->createSignatureLines()
                          ->getLines()
        ;
        $expectations = [
            ['"not-existing"', 'hm'],
            ['"@method"', 'POST'],
            ['"@authority"', 'game'],
        ];
        foreach ($expectations as $cnt => $item) {
            $line = $arr[$cnt];
            self::assertSame($item[0], $line->signatureComponent);
            self::assertSame($item[1], $line->valueToSign);
        }
    }
}
