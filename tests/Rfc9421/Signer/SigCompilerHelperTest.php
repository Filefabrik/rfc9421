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

use Filefabrik\Rfc9421\Enums\DerivedComponentEnum;
use Filefabrik\Rfc9421\Exceptions\EnumTypeException;
use Filefabrik\Rfc9421\Signature\SigningComponents;
use Filefabrik\Rfc9421\Signer\SigCompilerHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SigCompilerHelper::class)]
final class SigCompilerHelperTest extends TestCase
{
    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testMakeSig1fromComponents(): void
    {
        $components = new SigningComponents()->addName('test')
                                             ->addName('@authority')
                                             ->addName('try-out')
                                             ->addName('some-header')
        ;
        self::assertEquals(
            'someSig=("test" "@authority" "try-out" "some-header")',
            SigCompilerHelper::makeSig1fromComponents($components, 'someSig='),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testMakeSig1fromSignatureParams(): void
    {
        // todo status only on response signing
        $components = new SigningComponents()->addName('test')
                                             ->addName('@status')
                                             ->addName('try-out')
                                             ->addName('some-header')
        ;

        self::assertEquals(
            'sig1=("test" "@status" "try-out" "some-header")',
            new SigCompilerHelper($components, 'sig1=')->makeSig1fromSignatureParams(),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testMakeSig1fromConstructWithInitial(): void
    {
        $components = new SigningComponents()->addName('test')
                                             ->addName(DerivedComponentEnum::requestTarget)
                                             ->addName('try-out')
                                             ->addName('some-header')
        ;

        self::assertEquals(
            'sig1=("test" "@request-target" "try-out" "some-header")',
            new SigCompilerHelper($components, 'sig1=')->makeSig1fromSignatureParams(),
        );
    }

    /**
     * @throws \Filefabrik\Rfc9421\Exceptions\AlreadySetException
     * @throws \Filefabrik\Rfc9421\Exceptions\SignatureExtractionException
     */
    public function testMakeSig1fromConstructThrows(): void
    {
        $this->expectExceptionObject(EnumTypeException::TypeError('@test', DerivedComponentEnum::class));
        $components = new SigningComponents()->addName('test')
                                             ->addName('@test')
                                             ->addName('try-out')
                                             ->addName('some-header')
        ;
        new SigCompilerHelper($components, 'sig1=')->makeSig1fromSignatureParams();
    }
}
