<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\Class_\CoversAnnotationWithValueToAttributeRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector;
use Rector\ValueObject\PhpVersion;

return static function(RectorConfig $rectorConfig) {
    $rectorConfig
        ->paths([

            __DIR__.'/examples',
            __DIR__.'/src',
            __DIR__.'/tests',
        ])
    ;
    //  ->withSkipPath(__DIR__.'/vendor')
    // uncomment to reach your current PHP version

    $rectorConfig->sets(
        [
            SetList::CODE_QUALITY,
            SetList::DEAD_CODE,
            SetList::PRIVATIZATION,
            SetList::TYPE_DECLARATION,
            SetList::CODING_STYLE,
            LevelSetList::UP_TO_PHP_84,
        ],
    );
    $rectorConfig->rules([DeclareStrictTypesRector::class, CoversAnnotationWithValueToAttributeRector::class]);
    $rectorConfig->phpVersion(PhpVersion::PHP_84);
    $rectorConfig->phpstanConfig(__DIR__.'/phpstan.neon.dist');
};
