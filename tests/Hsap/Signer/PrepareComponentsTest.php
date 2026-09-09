<?php

declare(strict_types=1);

namespace Filefabrik\HsapTest\Signer;

use BadMethodCallException;
use Filefabrik\Hsap\Exceptions\AlreadySetException;
use Filefabrik\Hsap\Exceptions\InvalidDerivedKeyException;
use Filefabrik\Hsap\Exceptions\InvalidHeaderKeyException;
use Filefabrik\Hsap\SignatureParams;
use Filefabrik\Hsap\Signer\PrepareComponents;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use UnexpectedValueException;

#[CoversClass(PrepareComponents::class)]
final class PrepareComponentsTest extends TestCase
{
    public function testSetDefaultMethodsStack(): void
    {
        $orig = PrepareComponents::getDefaultDerivedStack();
        $arr  = [
            '@method' => fn(RequestInterface $message) => str_replace(
                't',
                'B',
                ucfirst(strtolower($message->getMethod())),
            ),

        ];
        PrepareComponents::setDefaultDerivedStack($arr);
        $r               = new Request('post', 'https://Test.com');
        $signatureParams = new SignatureParams();

        $signatureParams->setDerivedComponentName('@method');

        $prepareInstance = new PrepareComponents($r, $signatureParams);

        $this->assertSame('PosB', $prepareInstance->processValue('@method'));

        PrepareComponents::setDefaultDerivedStack($orig);
    }

    /**
     * @covers ::processValue
     * */
    public function testSetClosure(): void
    {
        $arr = [
            '@method' => fn(RequestInterface $message) => ucfirst(strtolower($message->getMethod())),

        ];

        $r               = new Request('post', 'https://Test.com');
        $signatureParams = new SignatureParams();

        $signatureParams->setDerivedComponentName('@method');

        $prepareInstance = new PrepareComponents($r, $signatureParams);
        $prepareInstance->setDerivedMapper($arr);
        $this->assertSame('Post', $prepareInstance->processValue('@method'));
    }

    /**
     * @covers ::processValue
     * @return void
     * @throws AlreadySetException
     * @throws InvalidDerivedKeyException
     */
    public function testProcessValueThrows(): void
    {
        $this->expectExceptionObject(
            new BadMethodCallException('Method "12" not callable.'),
        );
        $r               = new Request('post', 'https://Test.com/test&var=käse');
        $signatureParams = new SignatureParams();

        $prepareComponents          = new PrepareComponents($r, $signatureParams);
        $messageStack               = PrepareComponents::getDefaultDerivedStack();
        $messageStack[' bullshit '] = 12;
        $prepareComponents->setDerivedMapper($messageStack);

        $prepareComponents->processValue(' bullshit ');
    }

    /**
     * Overall Method to check the System
     * @covers ::processValue
     *
     * @return void
     * @throws AlreadySetException
     * @throws InvalidDerivedKeyException|InvalidHeaderKeyException
     */
    public function testProcessValue(): void
    {
        $r               = new Request('post', 'https://Test.com/var/pathy/?qer=var');
        $signatureParams = new SignatureParams();
        $signatureParams->setDerivedComponentName('@method')
                        ->add('@target-uri')
        ;

        $prepareInstance = new PrepareComponents($r, $signatureParams);

        $this->assertSame('POST', $prepareInstance->processValue('@method'));
        $this->assertSame('test.com/var/pathy/?qer=var', $prepareInstance->processValue('@target-uri'));
    }

    /**
     * @covers ::processTargetUri
     * @return void
     * @throws AlreadySetException
     * @throws InvalidDerivedKeyException
     */
    public function testProcessTargetUri(): void
    {
        $r = new Request('post', 'https://Test.com/var/pathy/?qer=var');

        $prepareInstance = new PrepareComponents($r, new SignatureParams());

        $this->assertSame('test.com/var/pathy/?qer=var', $prepareInstance->processValue('@target-uri'));
    }

    /**
     * @covers ::processAtMethod()
     * @return void
     */
    public function testProcessAtMethod(): void
    {
        $r               = new Request('post', 'https://Test.com/var/pathy/?qer=var');
        $prepareInstance = new PrepareComponents($r, new SignatureParams());
        $this->assertSame('POST', $prepareInstance->processValue('@method'));
    }

    /**
     * @covers ::processValue
     * @return void
     */
    public function testProcessValueNotCallable(): void
    {
        $this->expectExceptionObject(
            new UnexpectedValueException('Method "@method_notset" not registered.'),
        );
        $r               = new Request('post', 'https://Test.com');
        $signatureParams = new SignatureParams();

        $prepareInstance = new PrepareComponents($r, $signatureParams);
        $prepareInstance->processValue('@method_notset');
    }
}
