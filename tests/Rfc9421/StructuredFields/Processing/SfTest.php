<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\StructuredFields\Processing;

use Bakame\Http\StructuredFields\Dictionary;
use Filefabrik\Rfc9421\Exceptions\MethodException;
use Filefabrik\Rfc9421\Exceptions\StructuredFieldException;
use Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException;
use Filefabrik\Rfc9421\StructuredFields\Entry;
use Filefabrik\Rfc9421\StructuredFields\MethodsMapper;
use Filefabrik\Rfc9421\StructuredFields\Processing\Sf;
use Filefabrik\Rfc9421\StructuredFields\StructuredFields;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Sf::class)]
final class SfTest extends TestCase
{
    /**
     * @throws \Bakame\Http\StructuredFields\StructuredFieldError
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     */
    public function testHandle(): void
    {
        $mMapper = new MethodsMapper();
        $mMapper->add('dictionary', fn (string $fieldValue): Dictionary => Dictionary::fromHttpValue($fieldValue));
        $structuredFields = new StructuredFields();
        $structuredFields->add('Example-Dict', 'dictionary');
        $sfInstance = new Sf($mMapper, structuredFields: $structuredFields);
        $entry      = new Entry('example-dict', '  a=1, b=2;x=1;y=2, c=(a   b    c), d ');

        $res = $sfInstance->handle($entry);
        self::assertSame('a=1, b=2;x=1;y=2, c=(a b c), d', $res->value);
        self::assertSame('"example-dict";sf: a=1, b=2;x=1;y=2, c=(a b c), d', $res->parse());
    }

    public function testHandleThrowsNotNiceProceeded()
    {
        $this->expectExceptionObject(
            new UnProcessableSignatureException('Unknown or unregistered structured field type for "example-dict"'),
        );

        $mMapper = new MethodsMapper();
        $mMapper->add('dictionary', fn (): null => null);
        $structuredFields = new StructuredFields();
        $structuredFields->add('Example-Dict', 'dictionary');
        $sfInstance = new Sf($mMapper, structuredFields: $structuredFields);
        $entry      = new Entry('example-dict', '  a=1, b=2;x=1;y=2, c=(a   b    c), d ');

        $res = $sfInstance->handle($entry);
    }

    /**
     * @throws \Bakame\Http\StructuredFields\StructuredFieldError
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     */
    public function testHandleThrowsMissingMethod(): void
    {
        $this->expectExceptionObject(
            new MethodException('There is no registered Method "dictionary" to process the value.'),
        );
        $mMapper          = new MethodsMapper();
        $structuredFields = new StructuredFields();
        $structuredFields->add('Example-Dict', 'dictionary');
        $sfInstance = new Sf($mMapper, structuredFields: $structuredFields);
        $entry      = new Entry('example-dict', 'a=1, b=2;x=1;y=2, c=(a   b    c), d ');

        $sfInstance->handle($entry);
    }

    /**
     * @throws \Bakame\Http\StructuredFields\StructuredFieldError
     * @throws \Filefabrik\Rfc9421\Exceptions\UnProcessableSignatureException
     */
    public function testHandleThrowsNotMapped(): void
    {
        $this->expectExceptionObject(
            new StructuredFieldException('The field "example-dict" is not mapped into StructuredFields.'),
        );
        $mMapper          = new MethodsMapper();
        $structuredFields = new StructuredFields();
        $sfInstance       = new Sf($mMapper, structuredFields: $structuredFields);
        $entry            = new Entry('example-dict', 'a=1, b=2;x=1;y=2, c=(a   b    c), d ');

        $sfInstance->handle($entry);
    }
}
