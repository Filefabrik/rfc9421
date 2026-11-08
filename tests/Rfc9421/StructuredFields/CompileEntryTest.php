<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\StructuredFields;

use Filefabrik\Rfc9421\Dto\HeaderItemsDto;
use Filefabrik\Rfc9421\Enums\InitialContentsEnum;
use Filefabrik\Rfc9421\Signature\ComponentName;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421\StructuredFields\CompileEntry;
use Filefabrik\Rfc9421\StructuredFields\Entry;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CompileEntry::class)]
final class CompileEntryTest extends TestCase
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
        $instance = new CompileEntry(
            messageInterface    : $request,
            signingConfiguration: new SigningConfiguration(),
            componentItemDto    : ComponentName::fromString('test'),
            headerItemsDto      : HeaderItemsDto::fromMessageInterface($request),
        );
        $handled  = $instance->handle();

        self::assertEquals(new Entry('test', 'testValue'), $handled);
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
    public function testHandleMethod(): void
    {
        $request  = new Request('GET', 'my', ['test' => 'testValue']);
        $instance = new CompileEntry(
            messageInterface    : $request,
            signingConfiguration: new SigningConfiguration(),
            componentItemDto    : ComponentName::fromString('@method'),
            headerItemsDto      : HeaderItemsDto::fromMessageInterface($request),
        );
        $handled  = $instance->handle();

        self::assertEquals(new Entry('@method', 'GET'), $handled);
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
        $request     = new Request('GET', 'my', ['test' => 'testValue']);
        $instance    = new CompileEntry(
            messageInterface    : $request,
            signingConfiguration: new SigningConfiguration(),
            componentItemDto    : ComponentName::fromString('"test";bs'),
            headerItemsDto      : HeaderItemsDto::fromMessageInterface($request),
        );
        $handled     = $instance->handle();
        $targetEntry = new Entry(key: 'test', value: ':dGVzdFZhbHVl:', initialContents: InitialContentsEnum::bs);
        self::assertEquals($targetEntry, $handled);
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
        $request     = new Request('GET', 'my', ['test' => 'testValue']);
        $instance    = new CompileEntry(
            messageInterface    : $request,
            signingConfiguration: $config,
            componentItemDto    : ComponentName::fromString('"test";sf'),
            headerItemsDto      : HeaderItemsDto::fromMessageInterface($request),
        );
        $handled     = $instance->handle();
        $targetEntry = new Entry(
            key            : 'test',
            value          : 'testValue',
            initialContents: InitialContentsEnum::sf,
        );
        self::assertEquals($targetEntry, $handled);
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
    public function testHandleKey(): void
    {
        $config = new SigningConfiguration();
        // todo doc for the header test is need to have a structured field mapping
        // now we are using a list for test because it has key
        $config->getStructuredFields()
               ->add('test', 'list')
        ;
        $request     = new Request('GET', 'my', ['test' => '  a=1,    b=2;x=1;y=2,   d ']);
        $instance    = new CompileEntry(
            messageInterface    : $request,
            signingConfiguration: $config,
            componentItemDto    : ComponentName::fromString('"test";key="a"'),
            headerItemsDto      : HeaderItemsDto::fromMessageInterface($request),
        );
        $handled     = $instance->handle();
        $targetEntry = new Entry(
            key            : 'test',
            value          : '1',
            initialContents: InitialContentsEnum::key,
            nameOrKeyValue : 'a',
        );
        self::assertEquals($targetEntry, $handled);
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
    public function testHandleName(): void
    {
        $config = new SigningConfiguration();

        /**
         * todo doc name works atm only with the @query-param
         */
        $request     = new Request(
            'GET',
            'http://localhost:8001/path?param=value&foo=bar&baz=bat%2Dman&qux=',
            ['test' => '  a=1,    b=2;x=1;y=2,   d '],
        );
        $instance    = new CompileEntry(
            messageInterface    : $request,
            signingConfiguration: $config,
            componentItemDto    : ComponentName::fromString('"@query-param";name="baz"'),
            headerItemsDto      : HeaderItemsDto::fromMessageInterface($request),
        );
        $handled     = $instance->handle();
        $targetEntry = new Entry(
            key            : '@query-param',
            value          : 'bat-man',
            initialContents: InitialContentsEnum::name,
            nameOrKeyValue : 'baz',
        );
        $parsed      = $targetEntry->parse();
        self::assertSame('"@query-param";name="baz": bat-man', $parsed);
        self::assertEquals([$targetEntry->key, $targetEntry->value], [$handled->key, $handled->value]);
    }

    /**
     * @see https://www.rfc-editor.org/info/rfc9421/#section-2.2.8-6
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testHandleNameQux(): void
    {
        $config = new SigningConfiguration();

        $request  = new Request(
            'GET',
            'http://localhost:8001/path?param=value&foo=bar&baz=bat%2Dman&qux=',
            ['test' => '  a=1,    b=2;x=1;y=2,   d '],
        );
        $instance = new CompileEntry(
            messageInterface    : $request,
            signingConfiguration: $config,
            componentItemDto    : ComponentName::fromString('"@query-param";name="qux"'),
            headerItemsDto      : HeaderItemsDto::fromMessageInterface($request),
        );
        $handled  = $instance->handle();
        // note: empty value will be later parsed with space
        $targetEntry = new Entry(
            key            : '@query-param',
            value          : '',
            initialContents: InitialContentsEnum::name,
            nameOrKeyValue : 'qux',
        );
        self::assertEquals([$targetEntry->key, $targetEntry->value], [$handled->key, $handled->value]);
    }

    /**
     * @see https://www.rfc-editor.org/info/rfc9421/#section-2.2.8-6
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
        $config = new SigningConfiguration();

        $request     = new Request(
            'GET',
            'http://localhost:8001//parameters?var=this%20is%20a%20big%0Amultiline%20value&\
  bar=with+plus+whitespace&fa%C3%A7ade%22%3A%20=something',
            ['test' => '  a=1,    b=2;x=1;y=2,   d '],
        );
        $instance    = new CompileEntry(
            messageInterface    : $request,
            signingConfiguration: $config,
            componentItemDto    : ComponentName::fromString('"@query-param";name="var"'),
            headerItemsDto      : HeaderItemsDto::fromMessageInterface($request),
        );
        $handled     = $instance->handle();
        $targetEntry = new Entry(
            key            : '@query-param',
            value          : 'this%20is%20a%20big%0Amultiline%20value',
            initialContents: InitialContentsEnum::name,
            nameOrKeyValue : 'var',
        );
        self::assertEquals([$targetEntry->key, $targetEntry->value], [$handled->key, $handled->value]);
    }

}
