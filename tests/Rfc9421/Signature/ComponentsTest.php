<?php

declare(strict_types=1);

namespace Filefabrik\Rfc9421Test\Signature;

use Filefabrik\Rfc9421\Exceptions\AlreadySetException;
use Filefabrik\Rfc9421\Signature\Component;
use Filefabrik\Rfc9421\Signature\Components;
use Filefabrik\Rfc9421\Signature\InitialContentsEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Components::class)]
final class ComponentsTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     */
    public function testGetComponents(): void
    {
        $t = new Components()->set(new Component('test'))
                             ->set(new Component('test2', InitialContentsEnum::req))
        ;
        $this->assertEquals([
            new Component('test'),
            new Component('test2', 'req'),
        ], $t->getComponents());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     */
    public function testSetByName(): void
    {
        $t = new Components()->setByName('test')
                             ->setByName('test2', InitialContentsEnum::bs)
        ;
        $this->assertEquals([
            new Component('test'),
            new Component('test2', 'bs'),
        ], $t->getComponents());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     */
    public function testSet(): void
    {
        $t = new Components()->set(new Component('test'))
                             ->set(new Component('test2', InitialContentsEnum::req))
        ;
        $this->assertEquals([
            new Component('test'),
            new Component('test2', 'req'),
        ], $t->getComponents());
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     */
    public function testSetThrow(): void
    {
        $this->expectExceptionObject(new AlreadySetException('The Component "test2" already exists.'));
        new Components()->set(new Component('test'))
                        ->setByName('test2')
                        ->set(new Component('test2', InitialContentsEnum::req))
        ;
    }
}
