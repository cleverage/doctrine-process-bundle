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

namespace CleverAge\DoctrineProcessBundle\Tests\DependencyInjection;

use CleverAge\DoctrineProcessBundle\DependencyInjection\CleverAgeDoctrineProcessExtension;
use CleverAge\DoctrineProcessBundle\Task\Database\DatabaseReaderTask;
use CleverAge\DoctrineProcessBundle\Task\Database\DatabaseUpdaterTask;
use CleverAge\DoctrineProcessBundle\Task\EntityManager\ClearEntityManagerTask;
use CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineBatchWriterTask;
use CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineCleanerTask;
use CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineDetacherTask;
use CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineReaderTask;
use CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineRefresherTask;
use CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineRemoverTask;
use CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineWriterTask;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

#[CoversClass(CleverAgeDoctrineProcessExtension::class)]
class CleverAgeDoctrineProcessExtensionTest extends TestCase
{
    /**
     * @return iterable<string, array{string, class-string}>
     */
    public static function provideTasks(): iterable
    {
        yield 'database_reader' => ['cleverage_doctrine_process.task.database_reader', DatabaseReaderTask::class];
        yield 'database_updater' => ['cleverage_doctrine_process.task.database_updater', DatabaseUpdaterTask::class];
        yield 'doctrine_clear_entity_manager' => ['cleverage_doctrine_process.task.doctrine_clear_entity_manager', ClearEntityManagerTask::class];
        yield 'doctrine_batch_writer' => ['cleverage_doctrine_process.task.doctrine_batch_writer', DoctrineBatchWriterTask::class];
        yield 'doctrine_cleaner' => ['cleverage_doctrine_process.task.doctrine_cleaner', DoctrineCleanerTask::class];
        yield 'doctrine_detacher' => ['cleverage_doctrine_process.task.doctrine_detacher', DoctrineDetacherTask::class];
        yield 'doctrine_reader' => ['cleverage_doctrine_process.task.doctrine_reader', DoctrineReaderTask::class];
        yield 'doctrine_refresher' => ['cleverage_doctrine_process.task.doctrine_refresher', DoctrineRefresherTask::class];
        yield 'doctrine_remover' => ['cleverage_doctrine_process.task.doctrine_remover', DoctrineRemoverTask::class];
        yield 'doctrine_writer' => ['cleverage_doctrine_process.task.doctrine_writer', DoctrineWriterTask::class];
    }

    /**
     * @param class-string $class
     */
    #[DataProvider('provideTasks')]
    public function testTaskIsRegistered(string $id, string $class): void
    {
        $container = new ContainerBuilder();
        (new CleverAgeDoctrineProcessExtension())->load([], $container);

        $definition = $container->getDefinition($id);
        self::assertSame($class, $definition->getClass());
        // Tasks are stateful: each process execution must get its own instance
        self::assertFalse($definition->isShared());
        // Logged in the process tasks channel
        self::assertSame([['channel' => 'cleverage_process_task']], $definition->getTag('monolog.logger'));

        // Referenced as '@<class>' in process configurations
        $alias = $container->getAlias($class);
        self::assertSame($id, (string) $alias);
        self::assertTrue($alias->isPublic());
    }

    public function testEveryTaskIsTested(): void
    {
        $container = new ContainerBuilder();
        (new CleverAgeDoctrineProcessExtension())->load([], $container);

        $ids = array_filter(
            array_keys($container->getDefinitions()),
            static fn (string $id): bool => str_starts_with($id, 'cleverage_doctrine_process.task.')
        );
        self::assertEqualsCanonicalizing(array_column(iterator_to_array(self::provideTasks()), 0), array_values($ids));
    }
}
