<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Exceptions;

use Filefabrik\Rfc9421\Enums\InitialContentsEnum;
use Filefabrik\Rfc9421\Exceptions\StructuredFieldException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(className: StructuredFieldException::class)]
final class StructuredFieldExceptionTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     */
    public function testNotMapped(): void
    {
        $this->expectExceptionObject(
            new StructuredFieldException('The field "-not-mapped-" is not mapped into StructuredFields.'),
        );
        throw StructuredFieldException::notMapped('-not-mapped-');
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     */
    public function testMissingKeyOrNameKEY(): void
    {
        $this->expectExceptionObject(
            new StructuredFieldException('For the Entry-Field "-field-" there was no ;key="#missing#" key given.'),
        );
        throw StructuredFieldException::missingKeyOrName('-field-', InitialContentsEnum::key);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     */
    public function testMissingKeyOrNameNAME(): void
    {
        $this->expectExceptionObject(
            new StructuredFieldException(
                'For the Entry-Field "@query-param" there was no ;name="#missing#" key given.',
            ),
        );
        throw StructuredFieldException::missingKeyOrName('@query-param', InitialContentsEnum::name);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\StructuredFieldException
     */
    public function testUnknownStructuredFieldType(): void
    {
        $this->expectExceptionObject(
            new StructuredFieldException(
                'Unknown or unregistered structured field type for "example-dict".',
            ),
        );
        throw StructuredFieldException::unknownStructuredFieldType('example-dict');
    }
}
