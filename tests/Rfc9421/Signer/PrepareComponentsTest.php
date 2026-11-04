<?php

/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * State: under development
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Signer;

use Filefabrik\Rfc9421\Exceptions\PrepareMethodsException;
use Filefabrik\Rfc9421\Signer\PrepareComponents;
use Filefabrik\Rfc9421\SigningConfiguration;
use Filefabrik\Rfc9421\Support\PreparedMethods;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use UnexpectedValueException;

#[CoversClass(PrepareComponents::class)]
final class PrepareComponentsTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     */
    public function testSetDefaultDerivedStack(): void
    {
        $arr = [
            '@method' => fn (RequestInterface $message): string => str_replace(
                't',
                'B',
                ucfirst(strtolower($message->getMethod())),
            ),

        ];

        $prepareInstance = new PrepareComponents(new Request('post', 'https://Test.com'));
        $prepareInstance->addMappingValues($arr);

        self::assertSame('PosB', $prepareInstance->processValue('@method')->value);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     */
    public function testSetDerivedMapper(): void
    {
        $arr = [
            '@method' => fn (RequestInterface $message): string => ucfirst(strtolower($message->getMethod())),
        ];

        $r = new Request('post', 'https://Test.com');

        $prepareInstance = new PrepareComponents($r);
        $prepareInstance->addMappingValues($arr);
        self::assertSame('Post', $prepareInstance->processValue('@method')->value);
    }

    public function testSetPreparedMethods(): void
    {
        $instance = new PrepareComponents(new Request('post', 'https://Test.com'));
        $instance->setPreparedMethods(new PreparedMethods());

        self::assertSame([], $instance->preparedMethods->methods);
    }

    /**
     * Overall Method to check the System
     *
     *
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     */
    public function testProcessValue(): void
    {
        $r = new Request(
            'post',
            'https://Test.com/var/pathy/?qer=var',
            body: '{"busy": true, "message": "Your call is very important to us"}',
        );

        $prepareInstance = new PrepareComponents($r);

        self::assertSame(
            '"@method": POST',
            $prepareInstance->processValue('@method')
                            ->parse(),
        );
        self::assertSame(
            '"@target-uri": https://test.com/var/pathy/?qer=var',
            $prepareInstance->processValue('@target-uri')
                            ->parse(),
        );
        self::assertSame(
            '"@authority": test.com',
            $prepareInstance->processValue('@authority')
                            ->parse(),
        );
        self::assertSame(
            '"@scheme": https',
            $prepareInstance->processValue('@scheme')
                            ->parse(),
        );
        self::assertSame(
            '"@path": /var/pathy/',
            $prepareInstance->processValue('@path')
                            ->parse(),
        );
        self::assertSame(
            '"content-length": 62',
            $prepareInstance->processValue('content-length')
                            ->parse(),
        );
    }

    public function testProcessTargetUri(): void
    {
        $r = new Request('post', 'https://Test.com/var/pathy/?qer=var');

        $prepareInstance = new PrepareComponents($r);

        self::assertSame('https://test.com/var/pathy/?qer=var', $prepareInstance->processValue('@target-uri')->value);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     */
    public function testProcessingThrow(): void
    {
        $this->expectExceptionObject(new PrepareMethodsException('Method "missing" not registered.'));
        $r = new Request('post', 'https://Test.com/var/pathy/?qer=var');

        $prepareInstance = new PrepareComponents($r);

        $prepareInstance->processValue('missing');
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     */
    public function testProcessAtMethod(): void
    {
        $r               = new Request('post', 'https://Test.com/var/pathy/?qer=var');
        $prepareInstance = new PrepareComponents($r, signingConfiguration: new SigningConfiguration());
        self::assertSame('POST', $prepareInstance->processValue('@method')->value);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     */
    public function testProcessAuthority(): void
    {
        $r = new Request('post', 'https://Test.com/var/pathy/?qer=var');

        $prepareInstance = new PrepareComponents($r);

        self::assertSame('test.com', $prepareInstance->processValue('@authority')->value);

        $r = new Request('post', 'https://www.Test.com/var/pathy/?qer=var');

        self::assertSame('www.test.com', new PrepareComponents($r)->processValue('@authority')->value);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     */
    public function testProcessScheme(): void
    {
        $r = new Request('post', 'https://Test.com/var/pathy/?qer=var');

        $prepareInstance = new PrepareComponents($r);

        self::assertSame('https', $prepareInstance->processValue('@scheme')->value);

        /** @noinspection HttpUrlsUsage */
        $r = new Request('post', 'http://www.Test.com/var/pathy/?qer=var');

        self::assertSame('http', new PrepareComponents($r)->processValue('@scheme')->value);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     */
    public function testProcessPath(): void
    {
        $r = new Request('post', 'https://Test.com/var/pathy/?qer=var');

        $prepareInstance = new PrepareComponents($r);

        self::assertSame(
            '"@path": /var/pathy/',
            $prepareInstance->processValue('@path')
                            ->parse(),
        );

        /** @noinspection HttpUrlsUsage */
        $r = new Request('post', 'http://www.Test.com');

        self::assertSame(
            '"@path": /',
            new PrepareComponents($r)->processValue('@path')
                                     ->parse(),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     */
    public function testProcessValueNotCallable(): void
    {
        $this->expectExceptionObject(
            new PrepareMethodsException('Method "@method_notset" not registered.'),
        );
        $r = new Request('post', 'https://Test.com');

        $prepareInstance = new PrepareComponents($r);
        $prepareInstance->processValue('@method_notset');
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     */
    public function testSetMappingValues(): void
    {
        $arr = [
            '@methodsss' => fn (RequestInterface $message): string => ucfirst(strtolower($message->getMethod())),
        ];

        $r = new Request('post', 'https://Test.com');

        $prepareInstance = new PrepareComponents($r);
        $prepareInstance->addMappingValues($arr);
        self::assertSame('Post', $prepareInstance->processValue('@methodsss')->value);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     */
    public function testProcessContentLength(): void
    {
        $r = new Request(
            'post',
            '',
            body: '{"busy": true, "message": "Your call is very important to us"}',
        );

        $prepareInstance = new PrepareComponents($r);

        self::assertSame(62, $prepareInstance->processValue('content-length')->value);
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\PrepareMethodsException
     */
    public function testPrepareMethodsException(): void
    {
        $this->expectExceptionObject(
            new PrepareMethodsException('Error during calling the method with key: "testing": is a bug'),
        );

        $r = new Request('post', 'https://Test.com');

        $prepareInstance = new PrepareComponents(message: $r, preparedMethods: new PreparedMethods());
        $prepareInstance->preparedMethods->add(
            'testing',
            fn ($method) => throw new UnexpectedValueException('is a bug'),
        );
        $prepareInstance->processValue('testing');
    }

}
