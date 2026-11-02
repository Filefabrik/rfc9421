<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\StructuredFields;

use Filefabrik\Rfc9421\Exceptions\MethodException;
use Filefabrik\Rfc9421\StructuredFields\MethodsMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MethodsMapper::class)]
final class MethodsMapperTest extends TestCase
{
    public function testAdd(): void
    {
        $mapper = new MethodsMapper();
        $mapper->add('testMethod', fn () => true);
        self::assertCount(1, $mapper->methods);
    }

    public function testAddMany(): void
    {
        $mapper = new MethodsMapper();
        $mapper->addMany(['testMethod' => fn () => 'testMethodResult', 'testMethod2' => fn () => 'testMethod2Result']);
        self::assertCount(2, $mapper->methods);
        self::assertSame('testMethod', $mapper->methods['testMethod']->method);
        $m = $mapper->methods['testMethod']->closure;
        self::assertSame('testMethodResult', $m());
        self::assertSame('testMethod2', $mapper->methods['testMethod2']->method);
        $m = $mapper->methods['testMethod2']->closure;
        self::assertSame('testMethod2Result', $m());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     */
    public function testCallMethod(): void
    {
        $mapper = new MethodsMapper();
        $mapper->add('testMethod', fn (string $param) => $param.' returnVal');
        self::assertSame('prefiggs returnVal', $mapper->callMethod('testMethod', ['prefiggs']));
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     */
    public function testCallMethodThrows(): void
    {
        $this->expectExceptionObject(
            new MethodException('There is no registered Method "testMethod" to process the value.'),
        );
        $mapper = new MethodsMapper();

        self::assertSame('prefiggs returnVal', $mapper->callMethod('testMethod', ['prefiggs']));
    }

    public function testGetDefaultMapper(): void
    {
        self::assertCount(8, MethodsMapper::getDefaultMapper()->methods);
    }
}
