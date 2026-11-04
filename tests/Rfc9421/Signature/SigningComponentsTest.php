<?php
/*
 * Copyright (c) 2026.
 * e-mail: rt@filefabrik.com
 * mastodon: filefabrik@phpc.social
 * License: proprietary
 * todo insert correct copyright
 */
declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Signature;

use Filefabrik\Rfc9421\Enums\InitialContentsEnum;
use Filefabrik\Rfc9421\Exceptions\AlreadySetException;
use Filefabrik\Rfc9421\Signature\ComponentName;
use Filefabrik\Rfc9421\Signature\SigningComponents;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * todo more testing
 */
#[CoversClass(SigningComponents::class)]
final class SigningComponentsTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testSet(): void
    {
        $t = new SigningComponents()->addName('test')
                                    ->addWithContentsEnum('test2', InitialContentsEnum::req)
        ;

        $this->assertEquals([
            ComponentName::fromComponentName('test'),
            ComponentName::fromNameAndInitialContent('test2', 'req'),
        ], $t->getComponents());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testSetThrow(): void
    {
        $this->expectExceptionObject(new AlreadySetException('The Component "test2" already exists.'));
        new SigningComponents()->addName('test')
                               ->addName('test2')
                               ->addName('test2')
        ;
    }

}
