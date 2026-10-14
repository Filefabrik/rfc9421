<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Exceptions;

use Filefabrik\Rfc9421\Exceptions\AlreadySetException;
use Filefabrik\Rfc9421\Signature\DerivedComponentEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AlreadySetException::class)]
final class AlreadySetExceptionTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     */
    public function testInputSignature(): void
    {
        $this->expectExceptionObject(
            new AlreadySetException(
                'Signature-Input parameter "my-var-was-set" was set before and can not be set again.',
            ),
        );

        throw  AlreadySetException::inputSignature('my-var-was-set');
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     */
    public function testComponentExists(): void
    {
        $this->expectExceptionObject(
            new AlreadySetException(
                'The Component "@myVar-was-set" already exists',
            ),
        );

        throw  AlreadySetException::ComponentExists('@myVar-was-set');
    } /**
 * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
 */
    public function testComponentExistsEnum(): void
    {
        $this->expectExceptionObject(
            new AlreadySetException(
                'The Component "@authority" already exists',
            ),
        );

        throw  AlreadySetException::ComponentExists(DerivedComponentEnum::authority);
    }
}
