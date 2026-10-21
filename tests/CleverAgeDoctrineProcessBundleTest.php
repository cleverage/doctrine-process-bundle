<?php

declare(strict_types=1);

/*
 * This file is part of the CleverAge/DoctrineProcessBundle package.
 *
 * Copyright (c) Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\DoctrineProcessBundle\Tests;

use CleverAge\DoctrineProcessBundle\CleverAgeDoctrineProcessBundle;
use CleverAge\DoctrineProcessBundle\DependencyInjection\CleverAgeDoctrineProcessExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CleverAgeDoctrineProcessBundle::class)]
#[UsesClass(CleverAgeDoctrineProcessExtension::class)]
class CleverAgeDoctrineProcessBundleTest extends TestCase
{
    public function testPathIsTheBundleRoot(): void
    {
        $path = (new CleverAgeDoctrineProcessBundle())->getPath();

        self::assertSame(\dirname(__DIR__), $path);
        self::assertDirectoryExists($path.'/config/services');
    }

    public function testExtensionAlias(): void
    {
        $bundle = new CleverAgeDoctrineProcessBundle();
        $extension = $bundle->getContainerExtension();

        self::assertInstanceOf(CleverAgeDoctrineProcessExtension::class, $extension);
        self::assertSame('cleverage_doctrine_process', $extension->getAlias());
        // Built once
        self::assertSame($extension, $bundle->getContainerExtension());
    }
}
