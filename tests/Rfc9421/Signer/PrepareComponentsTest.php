<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Signer;

use BadMethodCallException;
use Filefabrik\Rfc9421\Exceptions\AlreadySetException;
use Filefabrik\Rfc9421\Exceptions\InvalidDerivedKeyException;
use Filefabrik\Rfc9421\Exceptions\InvalidHeaderKeyException;
use Filefabrik\Rfc9421\Signer\PrepareComponents;
use Filefabrik\Rfc9421\Signer\SignatureParams;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use UnexpectedValueException;

#[CoversClass(PrepareComponents::class)]
#[CoversMethod(PrepareComponents::class, 'processAuthority')]
#[CoversMethod(PrepareComponents::class, 'processScheme')]
#[CoversMethod(PrepareComponents::class, 'processPath')]
#[CoversMethod(PrepareComponents::class, 'processValue')]
final class PrepareComponentsTest extends TestCase
{
    public function testSetDefaultDerivedStack(): void
    {
        $orig = PrepareComponents::getDefaultDerivedStack();
        $arr  = [
            '@method' => fn(RequestInterface $message): string => str_replace(
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

    public function testSetDerivedMapper(): void
    {
        $arr = [
            '@method' => fn(RequestInterface $message): string => ucfirst(strtolower($message->getMethod())),
        ];

        $r               = new Request('post', 'https://Test.com');
        $signatureParams = new SignatureParams();

        $signatureParams->setDerivedComponentName('@method');

        $prepareInstance = new PrepareComponents($r);
        $prepareInstance->setDerivedMapper($arr);
        $this->assertSame('Post', $prepareInstance->processValue('@method'));
    }

    /**
     * @throws AlreadySetException
     * @throws InvalidDerivedKeyException
     */
    public function testProcessValueThrows(): void
    {
        $this->expectExceptionObject(
            new BadMethodCallException('Method "12" not callable.'),
        );
        $r = new Request('post', 'https://Test.com/test&var=käse');

        $prepareComponents = new PrepareComponents($r);
        $messageStack      = PrepareComponents::getDefaultDerivedStack();
        // todo never allow a number only closure or string
        $messageStack[' bullshit '] = '12';
        $prepareComponents->setDerivedMapper($messageStack);

        $prepareComponents->processValue(' bullshit ');
    }

    /**
     * Overall Method to check the System
     *
     *
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

    public function testProcessTargetUri(): void
    {
        $r = new Request('post', 'https://Test.com/var/pathy/?qer=var');

        $prepareInstance = new PrepareComponents($r);

        $this->assertSame('test.com/var/pathy/?qer=var', $prepareInstance->processTargetUri());
    }

    public function testProcessAtMethod(): void
    {
        $r               = new Request('post', 'https://Test.com/var/pathy/?qer=var');
        $prepareInstance = new PrepareComponents($r);
        $this->assertSame('POST', $prepareInstance->processAtMethod());
    }

    public function testProcessAuthority(): void
    {
        $r = new Request('post', 'https://Test.com/var/pathy/?qer=var');

        $prepareInstance = new PrepareComponents($r);

        $this->assertSame('test.com', $prepareInstance->processAuthority());

        $r = new Request('post', 'https://www.Test.com/var/pathy/?qer=var');

        $this->assertSame('www.test.com', new PrepareComponents($r)->processAuthority());
    }

    public function testProcessScheme(): void
    {
        $r = new Request('post', 'https://Test.com/var/pathy/?qer=var');

        $prepareInstance = new PrepareComponents($r);

        $this->assertSame('https', $prepareInstance->processScheme());

        $r = new Request('post', 'http://www.Test.com/var/pathy/?qer=var');

        $this->assertSame('http', new PrepareComponents($r)->processScheme());
    }

    public function testProcessPath(): void
    {
        $r = new Request('post', 'https://Test.com/var/pathy/?qer=var');

        $prepareInstance = new PrepareComponents($r);

        $this->assertSame('/var/pathy/', $prepareInstance->processPath());

        $r = new Request('post', 'http://www.Test.com');

        $this->assertSame('/', new PrepareComponents($r)->processPath());
    }

    public function testProcessValueNotCallable(): void
    {
        $this->expectExceptionObject(
            new UnexpectedValueException('Method "@method_notset" not registered.'),
        );
        $r = new Request('post', 'https://Test.com');

        $prepareInstance = new PrepareComponents($r);
        $prepareInstance->processValue('@method_notset');
    }

    public function testSetMappingValues(): void
    {
        $arr = [
            '@methodsss' => fn(RequestInterface $message): string => ucfirst(strtolower($message->getMethod())),
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
