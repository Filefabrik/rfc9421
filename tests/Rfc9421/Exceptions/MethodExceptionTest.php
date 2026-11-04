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

use Filefabrik\Rfc9421\Exceptions\MethodException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MethodException::class)]
final class MethodExceptionTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     */
    public function testNotCallable(): void
    {
        $this->expectExceptionObject(
            new MethodException('Missing method "testMethod" for MessageInterface "testclass"'),
        );
        throw MethodException::notCallable('testclass', 'testMethod');
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     */
    public function testReflectionCallError(): void
    {
        $this->expectExceptionObject(
            new MethodException('Closure Call Reflection Error with Message "some bug caller"'),
        );
        throw MethodException::reflectionCallError('some bug caller');
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     */
    public function testClosureCallError(): void
    {
        $this->expectExceptionObject(
            new MethodException('Closure Call Error with Message "some bug"'),
        );
        throw MethodException::closureCallError('some bug');
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\MethodException
     */
    public function testNotFoundInMethodsMapper(): void
    {
        $this->expectExceptionObject(
            new MethodException('There is no registered Method "example-dict" to process the value.'),
        );
        throw MethodException::notFoundInMethodsMapper('example-dict');
    }
}
