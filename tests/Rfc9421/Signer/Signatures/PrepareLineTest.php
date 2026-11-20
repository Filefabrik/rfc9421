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

use Filefabrik\Rfc9421\Signature\ComponentBuilder;
use Filefabrik\Rfc9421\Signature\ComponentName;
use Filefabrik\Rfc9421\Signer\Signatures\Line;
use Filefabrik\Rfc9421\Signer\Signatures\PrepareLine;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421\StructuredFields\EntryWithValue;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PrepareLine::class)]
final class PrepareLineTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testHandle(): void
    {
        $request  = new Request('GET', 'my', ['test' => 'testValue']);
        $instance = new PrepareLine(
            signingConfiguration: new SigningConfiguration(),
            componentItemDto    : ComponentName::fromString('test'),
            message             : $request,
        );
        $line     = $instance->handle();

        self::assertEquals(new Line('"test"', 'testValue'), $line);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testHandleBS(): void
    {
        $request = new Request('GET', 'my', ['test' => 'testValue']);

        $instance = new PrepareLine(
            signingConfiguration: new SigningConfiguration(),
            componentItemDto    : ComponentName::fromString('"test";bs'),
            message             : $request,
        );
        $line     = $instance->handle();

        $targetEntry = new Line(
            '"test";bs',
            EntryWithValue::fromBuilder(
                ComponentBuilder::from('test')
                                ->withBs(),
                ':dGVzdFZhbHVl:',
            )->value,
        );
        self::assertEquals($targetEntry, $line);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testHandleSF(): void
    {
        $config = new SigningConfiguration();
        // todo doc for the header test is need to have a structured field mapping
        $config->getStructuredFields()
               ->add('test', 'etag')
        ;
        $request = new Request('GET', 'my', ['test' => 'testValue']);

        $instance = new PrepareLine(
            signingConfiguration: $config,
            componentItemDto    : ComponentName::fromString('"test";sf'),
            message             : $request,
        );
        $line     = $instance->handle();

        $targetEntry = new Line(
            '"test";sf',
            EntryWithValue::fromBuilder(
                ComponentBuilder::from('test')
                                ->withSf(),
                'testValue',
            )->value);
        self::assertEquals($targetEntry, $line);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testHandleNameMultiline(): void
    {
        /** @noinspection HttpUrlsUsage */
        $request  = new Request(
            'GET',
            'http://my.com/parameters?var=this%20is%20a%20big%0Amultiline%20value&\
  bar=with+plus+whitespace&fa%C3%A7ade%22%3A%20=something',
            ['test' => 'testValue'],
        );
        $instance = new PrepareLine(
            signingConfiguration: new SigningConfiguration(),
            componentItemDto    : ComponentName::fromString('"@query-param";name="var"'),
            message             : $request,
        );
        $line     = $instance->handle();

        self::assertEquals(
            new Line('"@query-param";name="var"', 'this%20is%20a%20big%0Amultiline%20value'),
            $line,
        );
    }
}
