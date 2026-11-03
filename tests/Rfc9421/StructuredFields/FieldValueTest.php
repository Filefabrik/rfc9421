<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\StructuredFields;

use Filefabrik\Rfc9421\Dto\HeaderItemsDto;
use Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException;
use Filefabrik\Rfc9421\Signature\ComponentName;
use Filefabrik\Rfc9421\Enums\InitialContentsEnum;
use Filefabrik\Rfc9421\Signature\InitialContentsParameters;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421\StructuredFields\Entry;
use Filefabrik\Rfc9421\StructuredFields\FieldValue;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FieldValue::class)]
final class FieldValueTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     */
    public function testGetFieldValue(): void
    {
        $request = new Request('GET', 'my', ['test' => 'testValue']);

        $fw = new FieldValue(
            messageInterface         : $request,
            signingConfiguration     : new SigningConfiguration(),
            headerItemsDto           : HeaderItemsDto::fromMessageInterface($request),
            componentItemDto         : ComponentName::fromString('test'),
            initialContentsParameters: new InitialContentsParameters([]),
        );

        $handled = $fw->getFieldValue();

        self::assertEquals(new Entry('test', 'testValue'), $handled);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     */
    public function testGetFieldValueResponse(): void
    {
        $request = new Response(headers: ['test' => 'testValue']);

        $fw = new FieldValue(
            messageInterface         : $request,
            signingConfiguration     : new SigningConfiguration(),
            headerItemsDto           : HeaderItemsDto::fromMessageInterface($request),
            componentItemDto         : ComponentName::fromString('test'),
            initialContentsParameters: new InitialContentsParameters([]),
        );

        $handled = $fw->getFieldValue();

        self::assertEquals(new Entry('test', 'testValue'), $handled);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     */
    public function testHandleSF(): void
    {
        $config = new SigningConfiguration();
        // todo doc for the header test is need to have a structured field mapping
        $config->getStructuredFields()
               ->add('test', 'etag')
        ;
        $request     = new Request('GET', 'my', ['test' => 'testValue']);
        $instance    = new FieldValue(
            messageInterface         : $request,
            signingConfiguration     : $config,
            headerItemsDto           : HeaderItemsDto::fromMessageInterface($request),
            componentItemDto         : ComponentName::fromString('"test";sf'),
            initialContentsParameters: new InitialContentsParameters(),
        );
        $handled     = $instance->getFieldValue();
        $targetEntry = new Entry('test', 'testValue');
        self::assertEquals($targetEntry, $handled);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testHandleName(): void
    {
        $config = new SigningConfiguration();

        /**
         * todo doc name works atm only with the @query-param
         */
        $request     = new Request(
            'GET',
            'http://localhost:8001/path?param=value&foo=bar&baz=bat%2Dman',
            ['test' => '  a=1,    b=2;x=1;y=2,   d '],
        );
        $instance    = new FieldValue(
            messageInterface         : $request,
            signingConfiguration     : $config,
            headerItemsDto           : HeaderItemsDto::fromMessageInterface($request),
            componentItemDto         : ComponentName::fromString('"@query-param";name="baz"'),
            initialContentsParameters: new InitialContentsParameters()->setParameter(
                InitialContentsEnum::name,
                'baz',
            ),
        );
        $handled     = $instance->getFieldValue();
        $targetEntry = new Entry('baz', '"bat-man"', InitialContentsEnum::name);
        self::assertEquals([$targetEntry->key, $targetEntry->value], [$handled->key, $handled->value]);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testInvalidHeaderKeyException(): void
    {
        $this->expectExceptionObject(
            new InvalidHeaderKeyException(
                'The header "play" does not exist in the message. Note:headers can only be a part of the signing process if they are existing. $message->withHeader("play", \'your value\')',
            ),
        );
        $request = new Request('GET', 'my', ['test' => 'testValue']);

        $fw = new FieldValue(
            messageInterface         : $request,
            signingConfiguration     : new SigningConfiguration(),
            headerItemsDto           : HeaderItemsDto::fromMessageInterface($request),
            componentItemDto         : ComponentName::fromString('play'),
            initialContentsParameters: new InitialContentsParameters([]),
        );

        $fw->getFieldValue();
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testWithResponseInterface(): void
    {
        $request = new Response(headers: ['test' => 'testValue']);

        $fw = new FieldValue(
            messageInterface         : $request,
            signingConfiguration     : new SigningConfiguration(),
            headerItemsDto           : HeaderItemsDto::fromMessageInterface($request),
            componentItemDto         : ComponentName::fromString('"test";req'),
            initialContentsParameters: new InitialContentsParameters([InitialContentsEnum::req->name => true]),
        );

        $handled = $fw->getFieldValue();

        self::assertEquals(new Entry('test', 'testValue')->withREQEnum(), $handled);
    }
}
