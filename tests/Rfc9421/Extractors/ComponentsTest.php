<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Extractors;

use Bakame\Http\StructuredFields\Dictionary;
use Filefabrik\Rfc9421\Exceptions\SignatureExtractionException;
use Filefabrik\Rfc9421\Extractors\Components;
use Filefabrik\Rfc9421\Signature\Component;
use Filefabrik\Rfc9421\Signature\DerivedComponentEnum;
use Filefabrik\Rfc9421\Signature\InitialContentsEnum;
use Filefabrik\Rfc9421\Support\PreparedMethods;
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
        $input = '"content-length";req "@query-param";bs "example-dict";tr "digi-test"';

        $result = new Components($input)->extractToComponentsObject();

        $expects = new \Filefabrik\Rfc9421\Signature\Components()
            ->set(
                new Component('content-length', InitialContentsEnum::req),
            )
            ->set(
                new Component(
                    '@query-param',
                    InitialContentsEnum::bs,
                ),
            )
            ->set(
                new Component(
                    'example-dict',
                    InitialContentsEnum::tr,
                ),
            )
            ->set(
                new Component(
                    'digi-test',
                ),
            )
        ;
        self::assertEquals(
            $expects,
            $result,
        );
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

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testWithMethod(): void
    {
        $input = '"content-length";name "@query-param";name="baz": batman "example-dict";tr "digi-test"';

        $expects        = new \Filefabrik\Rfc9421\Signature\Components()
            ->set(
                new Component('content-length', InitialContentsEnum::req),
            )
            ->set(
                new Component(
                    '@query-param',
                    InitialContentsEnum::bs,
                ),
            )
            ->set(
                new Component(
                    'example-dict',
                    InitialContentsEnum::tr,
                ),
            )
            ->set(
                new Component(
                    'digi-test',
                ),
            )
        ;
        $prepareMethods = new PreparedMethods();
        $prepareMethods->add(DerivedComponentEnum::queryParam, function ($headerLine, $segmentName) {
       $headerLine=     str_replace('"'.$segmentName.'";','',$headerLine);

            $p = Dictionary::fromHttpValue($headerLine);
        });
        $result = new Components(
            componentsString: $input,
            preparedMethods : $prepareMethods,
        )->extractToComponentsObject();

        self::assertEquals(
            $expects,
            $result,
        );
    }

}
