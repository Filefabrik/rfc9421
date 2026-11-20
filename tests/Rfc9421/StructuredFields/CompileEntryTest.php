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
use Filefabrik\Rfc9421\Signature\ComponentBuilder;
use Filefabrik\Rfc9421\Signature\ComponentExtractor;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421\StructuredFields\CompileEntry;
use Filefabrik\Rfc9421\StructuredFields\EntryWithValue;
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
            componentItemDto    : ComponentExtractor::extractToComponentItemDto('test'),
            headerItemsDto      : HeaderItemsDto::fromMessageInterface($request),
        );
        $handled  = $instance->handle();

        self::assertEquals(EntryWithValue::fromBuilder(ComponentBuilder::from('test'), 'testValue'), $handled);
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
            componentItemDto    : ComponentExtractor::extractToComponentItemDto('@method'),
            headerItemsDto      : HeaderItemsDto::fromMessageInterface($request),
        );
        $handled  = $instance->handle();

        self::assertEquals(EntryWithValue::fromBuilder(ComponentBuilder::from('@method'), 'GET'), $handled);
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
        $request  = new Request('GET', 'my', ['test' => 'testValue']);
        $instance = new CompileEntry(
            messageInterface    : $request,
            signingConfiguration: new SigningConfiguration(),
            componentItemDto    : ComponentExtractor::extractToComponentItemDto('"test";bs'),
            headerItemsDto      : HeaderItemsDto::fromMessageInterface($request),
        );
        $handled  = $instance->handle();

        $targetEntry = EntryWithValue::fromBuilder(
            ComponentBuilder::from('test')
                            ->withBs(),
            ':dGVzdFZhbHVl:',
        );
        //  new Entry(key: 'test', value: ':dGVzdFZhbHVl:', initialContents: InitialContentsEnum::bs);
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
            componentItemDto    : ComponentExtractor::extractToComponentItemDto('"test";sf'),
            headerItemsDto      : HeaderItemsDto::fromMessageInterface($request),
        );
        $handled     = $instance->handle();
        $targetEntry = EntryWithValue::fromBuilder(
            ComponentBuilder::from('test')
                            ->withSf(),
            'testValue',
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
        $request  = new Request('GET', 'my', ['test' => '  a=1,    b=2;x=1;y=2,   d ']);
        $instance = new CompileEntry(
            messageInterface    : $request,
            signingConfiguration: $config,
            componentItemDto    : ComponentExtractor::extractToComponentItemDto('"test";key="a"'),
            headerItemsDto      : HeaderItemsDto::fromMessageInterface($request),
        );
        $handled  = $instance->handle();

        $targetEntry = EntryWithValue::fromBuilder(
            ComponentBuilder::from('test')
                            ->withKey('a'),
            '1',
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
         * todo doc fieldName works atm only with the @query-param
         */
        $request  = new Request(
            'GET',
            'http://localhost:8001/path?param=value&foo=bar&baz=bat%2Dman&qux=',
            ['test' => '  a=1,    b=2;x=1;y=2,   d '],
        );
        $instance = new CompileEntry(
            messageInterface    : $request,
            signingConfiguration: $config,
            componentItemDto    : ComponentExtractor::extractToComponentItemDto('"@query-param";name="baz"'),
            headerItemsDto      : HeaderItemsDto::fromMessageInterface($request),
        );
        $handled  = $instance->handle();

        $targetEntry = EntryWithValue::fromBuilder(
            ComponentBuilder::from('@query-param')
                            ->withName('baz'),
            'bat-man',
        );

        $parsed = $targetEntry->parseComponentNameAndValue();
        self::assertSame('"@query-param";name="baz": bat-man', $parsed);
        self::assertEquals(
            [$targetEntry->getFieldName(), $targetEntry->getValue()],
            [$handled->getFieldName(), $handled->getValue()]
        );
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
            componentItemDto    : ComponentExtractor::extractToComponentItemDto('"@query-param";name="qux"'),
            headerItemsDto      : HeaderItemsDto::fromMessageInterface($request),
        );
        $handled  = $instance->handle();
        // note: empty value will be later parsed with space

        $targetEntry = EntryWithValue::fromBuilder(
            ComponentBuilder::from('@query-param')
                            ->withName('qux'),
            '',
        );

        self::assertEquals(
            [$targetEntry->getFieldName(), $targetEntry->getValue()],
            [$handled->getFieldName(), $handled->getValue()]
        );
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
            componentItemDto    : ComponentExtractor::extractToComponentItemDto('"@query-param";name="var"'),
            headerItemsDto      : HeaderItemsDto::fromMessageInterface($request),
        );
        $handled     = $instance->handle();
        $targetEntry = EntryWithValue::fromBuilder(
            ComponentBuilder::from('@query-param')
                            ->withName('var'),
            'this%20is%20a%20big%0Amultiline%20value',
        );

        self::assertEquals(
            [$targetEntry->getFieldName(), $targetEntry->getComponent(), $targetEntry->getValue()],
            [$handled->getFieldName(), $handled->getComponent(), $handled->getValue()]
        );
    }

}
