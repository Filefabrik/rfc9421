<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Extractors;

use Filefabrik\Rfc9421\Exceptions\SignatureExtractionException;
use Filefabrik\Rfc9421\Extractors\Components;
use Filefabrik\Rfc9421\Signature\Component;
use Filefabrik\Rfc9421\Signature\InitialContentsEnum;
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

}
