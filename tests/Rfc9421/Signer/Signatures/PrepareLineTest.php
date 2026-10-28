<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Signer\Signatures;

use Filefabrik\Rfc9421\Signature\ComponentName;
use Filefabrik\Rfc9421\Signature\InitialContentsEnum;
use Filefabrik\Rfc9421\Signer\Signatures\Line;
use Filefabrik\Rfc9421\Signer\Signatures\PrepareLine;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421\StructuredFields\Entry;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PrepareLine::class)]
final class PrepareLineTest extends TestCase
{
    public function testHandle(): void
    {
        $request  = new Request('GET', 'my', ['test' => 'testValue']);
        $instance = new PrepareLine(
            signingConfiguration: new SigningConfiguration(),
            componentItemDto    : ComponentName::fromString('test'),
            message             : $request,
        );
        $line     = $instance->handle();

        self::assertEquals(new Line('"test"', '"test": testValue'), $line);
    }

    public function testHandleBS(): void
    {
        $request = new Request('GET', 'my', ['test' => 'testValue']);

        $instance = new PrepareLine(
            signingConfiguration: new SigningConfiguration(),
            componentItemDto    : ComponentName::fromString('"test";bs'),
            message             : $request,
        );
        $line     = $instance->handle();

        $targetEntry = new Line('"test";bs', new Entry('test', ':dGVzdFZhbHVl:', InitialContentsEnum::bs)->parse());
        self::assertEquals($targetEntry, $line);
    }

    public function testHandleSF(): void
    {
        $config = new SigningConfiguration();
        // todo doc for the header test is need to have a structured field mapping
        $config->getStructuredFields()
               ->add('test', 'etag')
        ;
        $request = new Request('GET', 'my', ['test' => 'testValue']);

        $instance = new PrepareLine(
            signingConfiguration: $config,
            componentItemDto    : ComponentName::fromString('"test";sf'),
            message             : $request,
        );
        $line     = $instance->handle();

        $targetEntry = new Line('"test";sf', new Entry('test', 'testValue', InitialContentsEnum::sf)->parse());
        self::assertEquals($targetEntry, $line);
    }

}
