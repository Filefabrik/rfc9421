<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Extractors;

use Bakame\Http\StructuredFields\Item;
use Filefabrik\Rfc9421\Exceptions\SignatureExtractionException;
use Filefabrik\Rfc9421\Extractors\DecodeSignatureComponentEntry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(DecodeSignatureComponentEntry::class)]
final class DecodeSignatureComponentEntryTest extends TestCase
{
    public static function stringProvider(): array
    {
        return [
            // buggy format errors
            [
                '"@query-param:  ',
                [],
            ], // buggy format errors
            [
                '@query-param:"',
                [],
            ],
            // missing space after
            [
                '"@query-param":',
                [],
            ],
            [
                '"example-dict";sf: a=1, b=2;x=1;y=2, c=(a b c), d',
                [
                    '"example-dict";sf: a=1, b=2;x=1;y=2, c=(a b c), d',
                    'example-dict',
                    ';sf',
                    'a=1, b=2;x=1;y=2, c=(a b c), d',
                ],
            ],
            [
                '"@query-param";name="baz": bat-man',
                ['"@query-param";name="baz": bat-man', '@query-param', ';name="baz"', 'bat-man'],
            ],
            [
                '"content-digest": sha-256=:qvoLOe+II0NxvDyMNg80z+xDv6aD9IkT9w8a340Q1Oo=:',
                [
                    '"content-digest": sha-256=:qvoLOe+II0NxvDyMNg80z+xDv6aD9IkT9w8a340Q1Oo=:',
                    'content-digest',
                    '',
                    'sha-256=:qvoLOe+II0NxvDyMNg80z+xDv6aD9IkT9w8a340Q1Oo=:',
                ],
            ],
            [
                '"content-type": application/json',
                ['"content-type": application/json', 'content-type', '', 'application/json'],
            ],
            [
                '"content-digest": sha-512=:ky7+tHa2jKBwuLnRdgxC418SwYUSuSk4NXiGsAYA6aKby9eVl2PTRsVjwEn1EjPQ93BxKbqzkCh58NqE1nL67A==:',
                [
                    '"content-digest": sha-512=:ky7+tHa2jKBwuLnRdgxC418SwYUSuSk4NXiGsAYA6aKby9eVl2PTRsVjwEn1EjPQ93BxKbqzkCh58NqE1nL67A==:',
                    'content-digest',
                    '',
                    'sha-512=:ky7+tHa2jKBwuLnRdgxC418SwYUSuSk4NXiGsAYA6aKby9eVl2PTRsVjwEn1EjPQ93BxKbqzkCh58NqE1nL67A==:',
                ],

            ],
            [
                '"@method": POST',
                ['"@method": POST', '@method', '', 'POST'],
            ],
            [
                '"x-origin-host": mytest.com',
                ['"x-origin-host": mytest.com', 'x-origin-host', '', 'mytest.com'],
            ],
            [
                '"example-dict";key="d": ?1',
                ['"example-dict";key="d": ?1', 'example-dict', ';key="d"', '?1'],
            ],
            [
                '"example-header";bs: :dmFsdWUsIHdpdGgsIGxvdHM=:, :b2YsIGNvbW1hcw==:',
                [
                    '"example-header";bs: :dmFsdWUsIHdpdGgsIGxvdHM=:, :b2YsIGNvbW1hcw==:',
                    'example-header',
                    ';bs',
                    ':dmFsdWUsIHdpdGgsIGxvdHM=:, :b2YsIGNvbW1hcw==:',
                ],
            ],
            [
                '"@signature-params": testValue=("content-type" "content-digest" "@query-param";name="baz" "@method" "x-origin-host" "example-dict";sf "example-dict";key="d" "example-header";bs);created=$currentDateTime;expires=$expireDateTime;nonce="nonce-now";keyid="http://127.0.0.1:8001/actor#main-key";alg="rsa-v1-5-sha512";tag="mastodon"',
                [
                    '"@signature-params": testValue=("content-type" "content-digest" "@query-param";name="baz" "@method" "x-origin-host" "example-dict";sf "example-dict";key="d" "example-header";bs);created=$currentDateTime;expires=$expireDateTime;nonce="nonce-now";keyid="http://127.0.0.1:8001/actor#main-key";alg="rsa-v1-5-sha512";tag="mastodon"',
                    '@signature-params',
                    '',
                    'testValue=("content-type" "content-digest" "@query-param";name="baz" "@method" "x-origin-host" "example-dict";sf "example-dict";key="d" "example-header";bs);created=$currentDateTime;expires=$expireDateTime;nonce="nonce-now";keyid="http://127.0.0.1:8001/actor#main-key";alg="rsa-v1-5-sha512";tag="mastodon"',
                ],
            ],
            // empty values
            [
                '"@query-param": ',
                ['"@query-param": ', '@query-param', '', ''],
            ],
            // two spaces, last space is value
            [
                '"@query-param":  ',
                ['"@query-param":  ', '@query-param', '', ' '],
            ],

        ];
    }

    public static function componentDtoProvider(): array
    {
        return [
            // buggy format errors
            //            [
            //                '"@query-param:  ',
            //                [],
            //            ], // buggy format errors
            //            [
            //                '@query-param:"',
            //                [],
            //            ],
            //            // missing space after
            //            [
            //                '"@query-param":',
            //                [],
            //            ],
            [
                '"@query-param";name="baz": bat-man',
                Item::fromString('"@query-param";name="baz"'),

            ],
            [
                '"example-dict";sf: a=1, b=2;x=1;y=2, c=(a b c), d',
                Item::fromString('"example-dict";sf'),

            ],

            [
                '"content-digest": sha-256=:qvoLOe+II0NxvDyMNg80z+xDv6aD9IkT9w8a340Q1Oo=:',
                Item::fromString('"content-digest"'),
            ],
            [
                '"content-type": application/json',
                Item::fromString('"content-type"'),

            ],

            [
                '"@method": POST',
                Item::fromString('"@method"'),

            ],

            [
                '"example-dict";key="d": ?1',
                Item::fromString('"example-dict";key="d"'),

            ],
            [
                '"example-header";bs: :dmFsdWUsIHdpdGgsIGxvdHM=:, :b2YsIGNvbW1hcw==:',
                Item::fromString('"example-header";bs'),

            ],

            // empty values
            [
                '"@query-param": ',
                Item::fromString('"@query-param"'),

            ],
            // two spaces, last space is value
            [
                '"@query-param":  ',
                Item::fromString('"@query-param"'),

            ],

        ];
    }

    #[DataProvider('stringProvider')]
    public function testExtract(string $line, array $expected): void
    {


        $matches = DecodeSignatureComponentEntry::extract($line);

        self::assertSame($expected, $matches);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    #[DataProvider('stringProvider')]
    public function testExtractThrow(string $line, array $expected): void
    {
        if (0 === count($expected)) {
            $this->expectExceptionObject(SignatureExtractionException::invalidLine($line));
        }

        $matches = DecodeSignatureComponentEntry::extractOrThrow($line);

        self::assertSame($expected, $matches);
    }

    /**
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    #[DataProvider('componentDtoProvider')]
    public function testToBuilder(string $line, ?Item $expectedItem): void
    {
        $item    = DecodeSignatureComponentEntry::toBuilder($line)->toComponentItemDto();
        $exValue = $expectedItem->value();
        $decoded = $item->getComponent();
        self::assertEquals($exValue, $decoded);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\EnumTypeException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\UnexpectedValue
     */
    public function testToLine(): void
    {
        $line = DecodeSignatureComponentEntry::toLine('"example-dict";sf: a=1, b=2;x=1;y=2, c=(a b c), d');

        self::assertEquals(
            ['"example-dict";sf', 'a=1, b=2;x=1;y=2, c=(a b c), d'],
            [$line->signatureComponent, $line->valueToSign]
        );
    }
}
