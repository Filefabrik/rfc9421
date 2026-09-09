<?php

declare(strict_types=1);

namespace Filefabrik\HsapTest\Signer;

use BadMethodCallException;
use Filefabrik\Hsap\Exceptions\AlreadySetException;
use Filefabrik\Hsap\Exceptions\InvalidDerivedKeyException;
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
        $orig = PrepareComponents::getDefaultMethodsStack();
        $arr  = [
            '@method' => fn(RequestInterface $message) => str_replace(
                't',
                'B',
                ucfirst(strtolower($message->getMethod())),
            ),

        ];
        PrepareComponents::setDefaultMethodsStack($arr);
        $r               = new Request('post', 'https://Test.com');
        $signatureParams = new SignatureParams();

        $signatureParams->setDerivedComponentName('@method');

        $prepareInstance = new PrepareComponents($r, $signatureParams);

        $this->assertSame('PosB', $prepareInstance->processValue('@method'));

        PrepareComponents::setDefaultMethodsStack($orig);
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
        $prepareInstance->setMethodStack($arr);
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
        $r               = new Request('post', 'https://Test.com');
        $signatureParams = new SignatureParams();

        $prepareComponents          = new PrepareComponents($r, $signatureParams);
        $messageStack               = PrepareComponents::getDefaultMethodsStack();
        $messageStack[' bullshit '] = 12;
        $prepareComponents->setMethodStack($messageStack);

        $prepareComponents->processValue(' bullshit ');
    }

    /**
     * @covers ::processValue
     * @return void
     * @throws AlreadySetException
     * @throws InvalidDerivedKeyException
     */
    public function testProcessValue(): void
    {
        $r               = new Request('post', 'https://Test.com');
        $signatureParams = new SignatureParams();
        $signatureParams->setDerivedComponentName('@method');

        $prepareInstance = new PrepareComponents($r, $signatureParams);

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
