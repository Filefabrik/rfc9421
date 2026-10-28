<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Support;

use Filefabrik\Rfc9421\Exceptions\MethodException;
use Filefabrik\Rfc9421\Support\MessageInterfaceHelper;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MessageInterfaceHelper::class)]
final class MessageInterfaceHelperTest extends TestCase
{
    public function testHasGetUri(): void
    {
        $request = new Request('POST', 'http://example.com/pl?some=task');
        self::assertSame('http://example.com/pl?some=task', (string)MessageInterfaceHelper::hasGetUri($request));
    }

    public function testHasGetUriThrow(): void
    {
        $this->expectExceptionObject(
            new MethodException('Missing method "getUri" for MessageInterface "GuzzleHttp\Psr7\Response"'),
        );
        MessageInterfaceHelper::hasGetUri(new Response());
    }
}
