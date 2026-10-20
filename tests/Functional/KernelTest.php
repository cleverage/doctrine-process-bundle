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

namespace CleverAge\DoctrineProcessBundle\Tests\Functional;

use CleverAge\DoctrineProcessBundle\Tests\App\TestKernel;
use CleverAge\ProcessBundle\Registry\ProcessConfigurationRegistry;
use CleverAge\ProcessBundle\Registry\TaskRegistry;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * The bundle in an application with DoctrineBundle and process-bundle (see TestKernel): the container compiles, the
 * tasks of the bundle are resolved with their dependencies.
 */
#[CoversNothing]
class KernelTest extends KernelTestCase
{
    private mixed $exceptionHandler;

    protected function setUp(): void
    {
        $this->exceptionHandler = $this->getExceptionHandler();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // The ErrorHandler registered when the kernel boots is not removed by the first Symfony 6.4 versions
        if ($this->getExceptionHandler() !== $this->exceptionHandler) {
            restore_exception_handler();
        }
    }

    public function testDoctrineIsResolved(): void
    {
        self::assertInstanceOf(ManagerRegistry::class, self::getContainer()->get('doctrine'));
    }

    public function testTasksAreResolvedByCode(): void
    {
        if (!class_exists(TaskRegistry::class)) {
            self::markTestSkipped('The tasks are referenced by code since cleverage/process-bundle 5.4.');
        }
        $processConfigurationRegistry = self::getContainer()->get('test.process_configuration_registry');
        self::assertInstanceOf(ProcessConfigurationRegistry::class, $processConfigurationRegistry);
        $taskRegistry = self::getContainer()->get('test.task_registry');
        self::assertInstanceOf(TaskRegistry::class, $taskRegistry);

        $processConfiguration = $processConfigurationRegistry->getProcessConfiguration('test.doctrine');
        foreach (TestKernel::TASKS as $code => $class) {
            self::assertInstanceOf($class, $taskRegistry->getTask($processConfiguration->getTaskConfiguration($code)), $code);
        }
    }

    private function getExceptionHandler(): mixed
    {
        $handler = set_exception_handler(null);
        restore_exception_handler();

        return $handler;
    }

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }
}
