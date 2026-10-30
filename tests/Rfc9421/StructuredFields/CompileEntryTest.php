<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\StructuredFields;

use Filefabrik\Rfc9421\Dto\HeaderItemsDto;
use Filefabrik\Rfc9421\Signature\ComponentName;
use Filefabrik\Rfc9421\Signature\InitialContentsEnum;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421\StructuredFields\CompileEntry;
use Filefabrik\Rfc9421\StructuredFields\Entry;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CompileEntry::class)]
final class CompileEntryTest extends TestCase
{
    public function testHandle(): void
    {
        $request  = new Request('GET', 'my', ['test' => 'testValue']);
        $instance = new CompileEntry(
            componentItemDto    : ComponentName::fromString('test'),
            signingConfiguration: new SigningConfiguration(),
            headerItemsDto      : HeaderItemsDto::fromMessageInterface($request),
            messageInterface    : $request,
        );
        $handled  = $instance->handle();

        self::assertEquals(new Entry('test', 'testValue'), $handled);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Bakame\Http\StructuredFields\StructuredFieldError
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     */
    public function testHandleBS(): void
    {
        $request     = new Request('GET', 'my', ['test' => 'testValue']);
        $instance    = new CompileEntry(
            componentItemDto    : ComponentName::fromString('"test";bs'),
            signingConfiguration: new SigningConfiguration(),
            headerItemsDto      : HeaderItemsDto::fromMessageInterface($request),
            messageInterface    : $request,
        );
        $handled     = $instance->handle();
        $targetEntry = new Entry('test', ':dGVzdFZhbHVl:', InitialContentsEnum::bs);
        self::assertEquals($targetEntry, $handled);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Bakame\Http\StructuredFields\StructuredFieldError
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
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
            componentItemDto    : ComponentName::fromString('"test";sf'),
            signingConfiguration: $config,
            headerItemsDto      : HeaderItemsDto::fromMessageInterface($request),
            messageInterface    : $request,
        );
        $handled     = $instance->handle();
        $targetEntry = new Entry('test', 'testValue', InitialContentsEnum::sf);
        self::assertEquals($targetEntry, $handled);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Bakame\Http\StructuredFields\StructuredFieldError
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
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
            componentItemDto    : ComponentName::fromString('"test";key="a"'),
            signingConfiguration: $config,
            headerItemsDto      : HeaderItemsDto::fromMessageInterface($request),
            messageInterface    : $request,
        );
        $handled     = $instance->handle();
        $targetEntry = new Entry('test', '"a": 1', InitialContentsEnum::key);
        self::assertEquals($targetEntry, $handled);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Bakame\Http\StructuredFields\StructuredFieldError
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
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
        $instance    = new CompileEntry(
            componentItemDto    : ComponentName::fromString('"@query-param";name="baz"'),
            signingConfiguration: $config,
            headerItemsDto      : HeaderItemsDto::fromMessageInterface($request),
            messageInterface    : $request,
        );
        $handled     = $instance->handle();
        $targetEntry = new Entry('baz', '"bat-man"', InitialContentsEnum::key);
        self::assertEquals([$targetEntry->key, $targetEntry->value], [$handled->key, $handled->value]);
    }
}
