<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Extractors;

use Filefabrik\Rfc9421\Exceptions\SignatureExtractionException;
use Filefabrik\Rfc9421\Extractors\Components;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Components::class)]
final class ComponentsTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testExtractToComponentsObject(): void
    {
        $input = [/*
             * 2.2.7. Query
             *
             * @see https://www.rfc-editor.org/info/rfc9421/#name-query
             */
                  '"@query-param";name="baz"',
                  '"@query-param";name="bat"',
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

        ];

        $input = implode(' ', $input);

        $result = new Components($input)->extractToComponentsObject();

        $validateable = [];
        foreach ($result->getComponents() as $item) {
            $validateable[] = $item->httpValue;
        }

        $line = implode(' ', $validateable);

        self::assertEquals($input, $line);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testExtractToComponentsWithBrace(): void
    {
        $input = [/*
             * 2.2.7. Query
             *
             * @see https://www.rfc-editor.org/info/rfc9421/#name-query
             */
                  '"@query-param";name="baz"',
                  '"@query-param";name="bat"',
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

        ];

        $input = implode(' ', $input);

        $result = new Components('('.$input.')')->extractToComponentsObject();

        $validateable = [];
        foreach ($result->getComponents() as $item) {
            $validateable[] = $item->httpValue;
        }

        $line = implode(' ', $validateable);

        self::assertEquals($input, $line);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testExtractSegmentWithError(): void
    {
        $this->expectExceptionObject(
            new SignatureExtractionException('Segment has unknown format ""content-length";"'),
        );
        $input = '"content-length"; "@query-param";bs "example-dict";tr';

        new Components($input)->extractToComponentsObject();
    }

}
