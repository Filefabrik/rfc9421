<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Signer;

use Filefabrik\Rfc9421\Exceptions\AlreadySetException;
use Filefabrik\Rfc9421\Exceptions\InvalidDerivedKeyException;
use Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException;
use Filefabrik\Rfc9421\Signer\SignatureParams;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SignatureParams::class)]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Filefabrik\Rfc9421\Signer\SignatureParams::class, 'add')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Filefabrik\Rfc9421\Signer\SignatureParams::class, 'fromArray')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Filefabrik\Rfc9421\Signer\SignatureParams::class, 'getSignatureParameters')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Filefabrik\Rfc9421\Signer\SignatureParams::class, 'exists')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Filefabrik\Rfc9421\Signer\SignatureParams::class, 'setHeaderName')]
final class SignatureParamsTest extends TestCase
{
    /**
     * @throws AlreadySetException
     * @throws InvalidDerivedKeyException
     * @throws InvalidHeaderKeyException
     */
    public function testAdd(): void
    {
        $instance = new SignatureParams()->add('testname')
                                         ->add('@testname')
                                         ->add('content-test')
                                         ->add('@test-derived-component')
        ;

        $this->assertSame(
            ['testname', '@testname', 'content-test', '@test-derived-component'],
            $instance->getSignatureParameters(),
        );
    }

    public function testFromArray(): void
    {
        $arr      = ['testname', '@test-derived-component', '@testname', 'content-test'];
        $instance = SignatureParams::fromArray($arr);

        $this->assertSame(
            ['testname', '@test-derived-component', '@testname', 'content-test',],
            $instance->getSignatureParameters(),
        );
    }

    /**
     * @throws AlreadySetException
     * @throws InvalidDerivedKeyException
     * @throws InvalidHeaderKeyException
     */
    public function testGetSignatureParameters(): void
    {
        $instance = new SignatureParams()->setHeaderName('testname')
                                         ->setDerivedComponentName('@testname')
                                         ->setHeaderName('content-test')
                                         ->setDerivedComponentName('@test-derived-component')
        ;

        $this->assertSame(
            ['testname', '@testname', 'content-test', '@test-derived-component'],
            $instance->getSignatureParameters(),
        );
    }

    /**
     * @throws AlreadySetException
     */
    public function testExists(): void
    {
        $instance = new SignatureParams()->addIfNotExists('testname')
                                         ->addIfNotExists('@testname')
        ;

        $this->assertTrue($instance->exists('testname'));
        $this->assertTrue($instance->exists('@testname'));
        $this->assertFalse($instance->exists('-testname'));
    }

    /**
     * @throws InvalidHeaderKeyException
     * @throws AlreadySetException
     */
    public function testSetHeaderName(): void
    {
        $set = new SignatureParams()->setHeaderName('testname');
        $this->assertInstanceOf(SignatureParams::class, $set);
    }

    /**
     * @throws InvalidHeaderKeyException
     * @throws AlreadySetException
     */
    public function testSetHeaderNameThrowsInvalidHeaderName(): void
    {
        $this->expectExceptionObject(
            new InvalidHeaderKeyException('Regular Header keys for signature does not start with "@"'),
        );
        new SignatureParams()->setHeaderName('@testname');
    }

    /**
     * @throws AlreadySetException
     */
    public function testAddIfNotExistsAdd(): void
    {
        $this->expectExceptionObject(
            new AlreadySetException('Signature-Input parameter "@testname" was set before and can not be set again.'),
        );
        new SignatureParams()->addIfNotExists('@testname')
                             ->addIfNotExists('@testname')
        ;
    }

    /**
     * @throws AlreadySetException
     */
    public function testAddIfNotExistsRegularHeader(): void
    {
        $this->expectExceptionObject(
            new AlreadySetException('Signature-Input parameter "testname" was set before and can not be set again.'),
        );
        new SignatureParams()->addIfNotExists('testname')
                             ->addIfNotExists('testname')
        ;
    }

    /**
     * @throws AlreadySetException
     */
    public function testAddIfNotExistsVarious(): void
    {
        $this->expectExceptionObject(
            new AlreadySetException('Signature-Input parameter "testname" was set before and can not be set again.'),
        );
        new SignatureParams()->addIfNotExists('testname')
                             ->addIfNotExists('@testname')
                             ->addIfNotExists('testname')
        ;
    }

    /**
     * @throws InvalidDerivedKeyException
     * @throws AlreadySetException
     */
    public function testSetDerivedComponentName(): void
    {
        $set = new SignatureParams()->setDerivedComponentName('@testname');
        $this->assertInstanceOf(SignatureParams::class, $set);
    }

    /**
     * @throws InvalidDerivedKeyException
     * @throws AlreadySetException
     */
    public function testSetDerivedComponentNameThrows(): void
    {
        $this->expectExceptionObject(new InvalidDerivedKeyException('Derived component name must start with "@"'));
        new SignatureParams()->setDerivedComponentName('testname');
    }

    /**
     * @throws InvalidDerivedKeyException
     * @throws AlreadySetException
     */
    public function testSetDerivedComponentNameAlreadyExists(): void
    {
        $this->expectExceptionObject(
            new AlreadySetException('Signature-Input parameter "@testname" was set before and can not be set again.'),
        );
        new SignatureParams()->setDerivedComponentName('@testname')
                             ->setDerivedComponentName('@testname')
        ;
    }
}
