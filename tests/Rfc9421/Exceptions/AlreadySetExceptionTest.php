<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Exceptions;

use Filefabrik\Rfc9421\Exceptions\AlreadySetException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AlreadySetException::class)]
final class AlreadySetExceptionTest extends TestCase
{
    public function testInputSignature(): void
    {
        $this->expectExceptionObject(
            new AlreadySetException(
                'Signature-Input parameter "my-var-was-set" was set before and can not be set again.',
            ),
        );

        throw  AlreadySetException::inputSignature('my-var-was-set');
    }
}
