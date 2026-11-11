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

    #[DataProvider('stringProvider')]
    public function testExtract($line, $expected): void
    {
        $matches = DecodeSignatureComponentEntry::extract($line);

        self::assertSame($expected, $matches);
    }

}
