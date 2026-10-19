<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\StructuredFields;

use Bakame\Http\StructuredFields\Dictionary;
use Bakame\Http\StructuredFields\InnerList;
use Bakame\Http\StructuredFields\Item;
use Bakame\Http\StructuredFields\OuterList;
use Bakame\Http\StructuredFields\Parameters;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421\StructuredFields\DestructEntry;
use Filefabrik\Rfc9421\StructuredFields\DestructHeaders;
use Filefabrik\Rfc9421\StructuredFields\MethodsMapper;
use Filefabrik\Rfc9421\StructuredFields\StructuredFields;
use GuzzleHttp\Psr7\Request;
use JsonException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DestructHeaders::class)]
final class DestructHeadersTest extends TestCase
{
    /**
     * @throws \Bakame\Http\StructuredFields\StructuredFieldError
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     * @throws JsonException
     */
    public function test__construct(): void
    {
        $methods = [
            'list'       => fn (string $fieldValue): OuterList => OuterList::fromHttpValue($fieldValue),
            'innerlist'  => fn (string $fieldValue): InnerList => InnerList::fromHttpValue($fieldValue),
            'parameters' => fn (string $fieldValue): Parameters => Parameters::fromHttpValue($fieldValue),
            'dictionary' => fn (string $fieldValue): Dictionary => Dictionary::fromHttpValue($fieldValue),
            'item'       => fn (string $fieldValue): Item => Item::fromHttpValue($fieldValue),
            'etag'       => fn (string $fieldValue): string => MethodsMapper::defaultETagMethod($fieldValue),
            'url'        => fn (string $fieldValue): string => '"'.$fieldValue.'"',
            'date'       => fn (string $fieldValue): string => '@'.strtotime($fieldValue),
        ];

        $methodsMapper = new MethodsMapper()->addMany($methods);
        $date          = gmdate('D, d M Y H:i:s T');
        $request       = new Request(
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

        /**
         * Use $request->withAddedHeader() method to provide an additional header with same name as an existing header.
         */
        $request = $request->withHeader('Example-Header', 'value, with, lots');
        $request = $request->withAddedHeader('Example-Header', 'of, commas');
        $request = $request->withHeader('If-None-Match', 'W/"abcdef", "ghijkl", *');

        $parts = [
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
            '"@query-param";name="baz"',
            '"@query-param";name="bat"',
        ];

        $coveredFields = '('.implode(' ', $parts).')';

        $destructHeaders = new DestructHeaders(
            lowerCasedHeaderLines: DestructHeaders::getLowerCasedHeadersFromInterface($request),
            signatureParamsLine  : $coveredFields,
            message              : $request,
            originalRequest      : $request,
            methodsMapper        : $methodsMapper,
            signingConfiguration : $config,
        );

        $destructHeaders->setStructuredFieldType('example-dict', 'dictionary');
        $normalised = $destructHeaders->start();

        self::assertContains('"example-dict": a=1,    b=2;x=1;y=2,   c=(a   b   c), d', $normalised);
        self::assertContains('"example-dict";sf: a=1, b=2;x=1;y=2, c=(a b c), d', $normalised);
        self::assertContains('"example-dict";key="a": 1', $normalised);
        self::assertContains('"example-dict";key="b": 2;x=1;y=2', $normalised);
        self::assertContains('"example-dict";key="c": (a b c)', $normalised);
        self::assertContains('"example-dict";key="d": ?1', $normalised);
        self::assertContains('"example-header";bs: :dmFsdWUsIHdpdGgsIGxvdHM=:, :b2YsIGNvbW1hcw==:', $normalised);
        self::assertContains('"@method": GET', $normalised);
        self::assertContains('"@path": /resource', $normalised);
        self::assertContains('"host": api.example.com', $normalised);
        self::assertContains('"if-none-match";sf: "abcdef"; w, "ghijkl", *', $normalised);
        self::assertContains('"date": '.$date, $normalised);
        self::assertContains('"date";sf: @'.strtotime($date), $normalised);
        self::assertContains('"@request-target": /resource?bat&baz=3', $normalised);
        self::assertContains('"@target-uri": https://api.example.com/resource?bat&baz=3', $normalised);
        self::assertContains('"_baz_": "3"', $normalised);
        self::assertContains('"_bat_": ', $normalised);
    }

    /**
     * @throws \Bakame\Http\StructuredFields\StructuredFieldError
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     * @throws JsonException
     */
    public function testItemField(): void
    {
        $methods = [
            'list'       => fn (string $fieldValue): OuterList => OuterList::fromHttpValue($fieldValue),
            'innerlist'  => fn (string $fieldValue): InnerList => InnerList::fromHttpValue($fieldValue),
            'parameters' => fn (string $fieldValue): Parameters => Parameters::fromHttpValue($fieldValue),
            'dictionary' => fn (string $fieldValue): Dictionary => Dictionary::fromHttpValue($fieldValue),
            'item'       => fn (string $fieldValue): Item => Item::fromHttpValue($fieldValue),
            'etag'       => fn (string $fieldValue): string => MethodsMapper::defaultETagMethod($fieldValue),
            'url'        => fn (string $fieldValue): string => '"'.$fieldValue.'"',
            'date'       => fn (string $fieldValue): string => '@'.strtotime($fieldValue),
        ];

        $methodsMapper = new MethodsMapper()->addMany($methods);
        $date          = gmdate('D, d M Y H:i:s T');
        $request       = new Request(
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

        /**
         * Use $request->withAddedHeader() method to provide an additional header with same name as an existing header.
         */
        $request       = $request->withHeader('Example-Header', 'value, with, lots');
        $request       = $request->withAddedHeader('Example-Header', 'of, commas');
        $request       = $request->withHeader('If-None-Match', 'W/"abcdef", "ghijkl", *');

        $destructEntry = new DestructEntry(
            fieldItem           : Item::fromHttpValue('"example-dict";key="a"'),
            methodsMapper       : $methodsMapper,
            structuredFields    : StructuredFields::fromDefaultList()
                                                  ->add('example-dict', 'dictionary'),
            signingConfiguration: $config,
        );
        $headerArray   = DestructHeaders::getLowerCasedHeadersFromInterface($request);

        $normalised = $destructEntry->canonicalizeComponent($headerArray, $request);
        $parsed     = $normalised->parse();
        self::assertSame('"example-dict";key="a": 1', $parsed);
    }
}
