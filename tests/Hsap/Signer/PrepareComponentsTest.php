<?php

declare(strict_types=1);

namespace Filefabrik\HsapTest\Signer;

use BadMethodCallException;
use Filefabrik\Hsap\Exceptions\AlreadySetException;
use Filefabrik\Hsap\Exceptions\InvalidDerivedKeyException;
use Filefabrik\Hsap\Exceptions\InvalidHeaderKeyException;
use Filefabrik\Hsap\Signer\PrepareComponents;
use Filefabrik\Hsap\Signer\SignatureParams;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use UnexpectedValueException;

#[CoversClass(PrepareComponents::class)]
final class PrepareComponentsTest extends TestCase
{
    /**
     * @covers ::setDefaultDerivedStack
     * */
    public function testSetDefaultDerivedStack(): void
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

        $prepareInstance = new PrepareComponents($r);

        $this->assertSame('PosB', $prepareInstance->processValue('@method'));

        PrepareComponents::setDefaultDerivedStack($orig);
    }

    /**
     * @covers ::setDerivedMapper
     * */
    public function testSetDerivedMapper(): void
    {
        $arr = [
            '@method' => fn(RequestInterface $message) => ucfirst(strtolower($message->getMethod())),
        ];

        $r               = new Request('post', 'https://Test.com');
        $signatureParams = new SignatureParams();

        $signatureParams->setDerivedComponentName('@method');

        $prepareInstance = new PrepareComponents($r);
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

        $prepareComponents          = new PrepareComponents($r);
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
        $r = new Request(
            'post',
            'https://Test.com/var/pathy/?qer=var',
            body: '{"busy": true, "message": "Your call is very important to us"}',
        );

        $prepareInstance = new PrepareComponents($r);

        $this->assertSame('POST', $prepareInstance->processValue('@method'));
        $this->assertSame('test.com/var/pathy/?qer=var', $prepareInstance->processValue('@target-uri'));
        $this->assertSame('test.com', $prepareInstance->processValue('@authority'));
        $this->assertSame('https', $prepareInstance->processValue('@scheme'));
        $this->assertSame('/var/pathy/', $prepareInstance->processValue('@path'));
        $this->assertSame(62, $prepareInstance->processValue('content-length'));
    }

    /**
     * @covers ::processTargetUri
     * @return void
     */
    public function testProcessTargetUri(): void
    {
        $r = new Request('post', 'https://Test.com/var/pathy/?qer=var');

        $prepareInstance = new PrepareComponents($r);

        $this->assertSame('test.com/var/pathy/?qer=var', $prepareInstance->processTargetUri());
    }

    /**
     * @covers ::processAtMethod
     * @return void
     */
    public function testProcessAtMethod(): void
    {
        $r               = new Request('post', 'https://Test.com/var/pathy/?qer=var');
        $prepareInstance = new PrepareComponents($r);
        $this->assertSame('POST', $prepareInstance->processAtMethod());
    }

    /**
     * @covers ::processAuthority
     * @return void
     */
    public function testProcessAuthority(): void
    {
        $r = new Request('post', 'https://Test.com/var/pathy/?qer=var');

        $prepareInstance = new PrepareComponents($r);

        $this->assertSame('test.com', $prepareInstance->processAuthority());

        $r = new Request('post', 'https://www.Test.com/var/pathy/?qer=var');

        $this->assertSame('www.test.com', new PrepareComponents($r)->processAuthority());
    }

    /**
     * @covers ::processScheme
     * @return void
     */
    public function testProcessScheme(): void
    {
        $r = new Request('post', 'https://Test.com/var/pathy/?qer=var');

        $prepareInstance = new PrepareComponents($r);

        $this->assertSame('https', $prepareInstance->processScheme());

        $r = new Request('post', 'http://www.Test.com/var/pathy/?qer=var');

        $this->assertSame('http', new PrepareComponents($r)->processScheme());
    }

    /**
     * @covers ::processPath
     * @return void
     */
    public function testProcessPath(): void
    {
        $r = new Request('post', 'https://Test.com/var/pathy/?qer=var');

        $prepareInstance = new PrepareComponents($r);

        $this->assertSame('/var/pathy/', $prepareInstance->processPath());

        $r = new Request('post', 'http://www.Test.com');

        $this->assertSame('/', new PrepareComponents($r)->processPath());
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
        $r = new Request('post', 'https://Test.com');

        $prepareInstance = new PrepareComponents($r);
        $prepareInstance->processValue('@method_notset');
    }



    public function testSetMappingValues()
    {
        $arr = [
            '@methodsss' => fn(RequestInterface $message) => ucfirst(strtolower($message->getMethod())),
        ];

        $r = new Request('post', 'https://Test.com');

        $prepareInstance = new PrepareComponents($r);
        $prepareInstance->setMappingValues($arr);
        $this->assertSame('Post', $prepareInstance->processValue('@methodsss'));
    }

    public function testProcessContentLength(): void
    {
        $r = new Request(
            'post',
            '',
            body: '{"busy": true, "message": "Your call is very important to us"}',
        );

        $prepareInstance = new PrepareComponents($r);

        $this->assertSame(62, $prepareInstance->processContentLength());
    }
}
