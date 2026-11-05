<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Stuff;

use Bakame\Http\StructuredFields\Item;
use Exception;
use Filefabrik\Rfc9421\Dto\ComponentItemDto;
use Filefabrik\Rfc9421\Dto\HeaderItemsDto;
use Filefabrik\Rfc9421\Exceptions\StructuredFieldException;
use Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421\StructuredFields\CompileEntry;
use Filefabrik\Rfc9421\Stuff\DestructHeaders;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DestructHeaders::class)]
final class DestructHeadersTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     */
    public function test__construct(): void
    {
        $date    = gmdate('D, d M Y H:i:s T');
        $request = new Request(
            'GET',
            'https://api.example.com/resource?bat&baz=3',
            [
                'Host'         => 'api.example.com',
                'Date'         => $date,
                'x-test'       => '',
                /*
                * @see https://www.rfc-editor.org/info/rfc9421/#name-strict-serialization-of-htt
                */
                'Example-Dict' => '  a=1,    b=2;x=1;y=2,   c=(a   b   c), d ',
            ],
        );

        $config = new SigningConfiguration();
        $config->getStructuredFields()
               ->add('example-dict', 'dictionary')
        ;
        /**
         * Use $request->withAddedHeader() method to provide an additional header with same name as an existing header.
         */
        $request = $request->withHeader('Example-Header', 'value, with, lots');
        $request = $request->withAddedHeader('Example-Header', 'of, commas');
        $request = $request->withHeader('If-None-Match', 'W/"abcdef", "ghijkl", *');

        $signingParts = [
            '"example-dict"',
            '"example-dict";sf',
            '"example-dict";key="a"',
            '"example-dict";key="b"',
            '"example-dict";key="c"',
            '"example-dict";key="d"',
            '"content-length"',
            '"@method"',
            '"example-header";bs',
            '"@path"',
            '"host"',
            '"if-none-match";sf',
            '"date"',
            '"date";sf',
            '"@request-target"',
            '"@target-uri"',
            /*
             * 2.2.7. Query
             *
             * @see https://www.rfc-editor.org/info/rfc9421/#name-query
             */
            '"@query-param";name="baz"',
            '"@query-param";name="bat"',
        ];

        $signatureParamsLine = '('.implode(' ', $signingParts).')';

        $destructHeaders = new DestructHeaders(
            headerItemsDto      : HeaderItemsDto::fromMessageInterface($request),
            signatureParamsLine : $signatureParamsLine,
            message             : $request,
            originalRequest     : $request,
            signingConfiguration: $config,
        );

        $parsedArray = $destructHeaders->start();

        /*
         * @see https://www.rfc-editor.org/info/rfc9421/#name-strict-serialization-of-htt
         */
        self::assertContains('"example-dict": a=1,    b=2;x=1;y=2,   c=(a   b   c), d', $parsedArray);
        self::assertContains('"example-dict";sf: a=1, b=2;x=1;y=2, c=(a b c), d', $parsedArray);
        self::assertContains('"example-dict";key="a": 1', $parsedArray);
        self::assertContains('"example-dict";key="b": 2;x=1;y=2', $parsedArray);
        self::assertContains('"example-dict";key="c": (a b c)', $parsedArray);
        self::assertContains('"example-dict";key="d": ?1', $parsedArray);

        self::assertContains('"example-header";bs: :dmFsdWUsIHdpdGgsIGxvdHM=:, :b2YsIGNvbW1hcw==:', $parsedArray);
        self::assertContains('"@method": GET', $parsedArray);
        self::assertContains('"@path": /resource', $parsedArray);
        self::assertContains('"host": api.example.com', $parsedArray);
        self::assertContains('"if-none-match";sf: "abcdef"; w, "ghijkl", *', $parsedArray);
        self::assertContains('"date": '.$date, $parsedArray);
        self::assertContains('"date";sf: @'.strtotime($date), $parsedArray);
        self::assertContains('"@request-target": /resource?bat&baz=3', $parsedArray);
        self::assertContains('"@target-uri": https://api.example.com/resource?bat&baz=3', $parsedArray);
        self::assertContains('"@query-param";name="baz": 3', $parsedArray);
        self::assertContains('"@query-param";name="bat": ', $parsedArray);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     * @throws Exception
     */
    public function testItemField(): void
    {
        $date    = gmdate('D, d M Y H:i:s T');
        $request = new Request(
            'GET',
            'https://api.example.com/resource?bat&baz=3',
            [
                'Host'         => 'api.example.com',
                'Date'         => $date,
                'x-test'       => '',
                'Example-Dict' => '  a=1,    b=2;x=1;y=2,   c=(a   b   c), d ',
            ],
        );

        $config = new SigningConfiguration();
        $config->getStructuredFields()
               ->add('example-dict', 'dictionary')
        ;
        /**
         * Use $request->withAddedHeader() method to provide an additional header with same name as an existing header.
         */
        $request = $request->withHeader('Example-Header', 'value, with, lots');
        $request = $request->withAddedHeader('Example-Header', 'of, commas');
        $request = $request->withHeader('If-None-Match', 'W/"abcdef", "ghijkl", *');

        $destructEntry = new CompileEntry(
            messageInterface    : $request,
            signingConfiguration: $config,
            componentItemDto    : ComponentItemDto::fromFieldItem(Item::fromHttpValue('"example-dict";sf')),
            headerItemsDto      : HeaderItemsDto::fromMessageInterface($request),
        );

        $compiledField = $destructEntry->handle();
        $parsed        = $compiledField->parse();
        self::assertSame('"example-dict";sf: a=1, b=2;x=1;y=2, c=(a b c), d', $parsed);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     */
    public function testDouble(): void
    {
        $this->expectExceptionObject(
            new UnprocessableSignatureException('Duplicate item found in singable components ""example-dict";key="b""'),
        );
        $date    = gmdate('D, d M Y H:i:s T');
        $request = new Request(
            'GET',
            'https://api.example.com/resource?bat&baz=3',
            [
                'Host'         => 'api.example.com',
                'Date'         => $date,
                'x-test'       => '',
                /*
                * @see https://www.rfc-editor.org/info/rfc9421/#name-strict-serialization-of-htt
                */
                'Example-Dict' => '  a=1,    b=2;x=1;y=2,   c=(a   b   c), d ',
            ],
        );

        $config = new SigningConfiguration();
        $config->getStructuredFields()
               ->add('example-dict', 'dictionary')
        ;
        /**
         * Use $request->withAddedHeader() method to provide an additional header with same name as an existing header.
         */
        $request = $request->withHeader('Example-Header', 'value, with, lots');
        $request = $request->withAddedHeader('Example-Header', 'of, commas');
        $request = $request->withHeader('If-None-Match', 'W/"abcdef", "ghijkl", *');

        $signingParts = [
            '"example-dict";key="b"',
            '"example-dict";key="b"',

        ];

        $signatureParamsLine = '('.implode(' ', $signingParts).')';

        $destructHeaders = new DestructHeaders(
            headerItemsDto      : HeaderItemsDto::fromMessageInterface($request),
            signatureParamsLine : $signatureParamsLine,
            message             : $request,
            originalRequest     : $request,
            signingConfiguration: $config,
        );

        $destructHeaders->start();
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     */
    public function testThrows(): void
    {
        $this->expectExceptionObject(
            new StructuredFieldException('The HTTP textual representation is missing an excepted comma.'),
        );
        $date    = gmdate('D, d M Y H:i:s T');
        $request = new Request(
            'GET',
            'https://api.example.com/resource?bat&baz=3',
            [
                'Host'         => 'api.example.com',
                'Date'         => $date,
                'x-test'       => '',
                /*
                * @see https://www.rfc-editor.org/info/rfc9421/#name-strict-serialization-of-htt
                */
                'Example-Dict' => '  a=1,    b=2;x=1;y=2,   c=(a   b   c), d ###;;afds',
            ],
        );

        $config = new SigningConfiguration();
        $config->getStructuredFields()
               ->add('example-dict', 'dictionary')
        ;
        /**
         * Use $request->withAddedHeader() method to provide an additional header with same name as an existing header.
         */
        $request = $request->withHeader('Example-Header', 'value, with, lots');
        $request = $request->withAddedHeader('Example-Header', 'of, commas');
        $request = $request->withHeader('If-None-Match', 'W/"abcdef", "ghijkl", *');

        $signingParts = [
            '"example-dict";key="b"',
            '"example-dict";key="b"',

        ];

        $signatureParamsLine = '('.implode(' ', $signingParts).')';

        $destructHeaders = new DestructHeaders(
            headerItemsDto      : HeaderItemsDto::fromMessageInterface($request),
            signatureParamsLine : $signatureParamsLine,
            message             : $request,
            originalRequest     : $request,
            signingConfiguration: $config,
        );

        $destructHeaders->start();
    }
}
