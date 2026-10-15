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
use Symfony\Component\DependencyInjection\Reference;

#[CoversClass(CleverAgeDoctrineProcessExtension::class)]
class CleverAgeDoctrineProcessExtensionTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string, string, string}>
     */
    public static function provideTasks(): iterable
    {
        yield 'database_reader' => [DatabaseReaderTask::class, 'doctrine_database_reader', 'cleverage_doctrine_process.task.database_reader'];
        yield 'database_updater' => [DatabaseUpdaterTask::class, 'doctrine_database_updater', 'cleverage_doctrine_process.task.database_updater'];
        yield 'doctrine_clear_entity_manager' => [ClearEntityManagerTask::class, 'doctrine_clear_entity_manager', 'cleverage_doctrine_process.task.doctrine_clear_entity_manager'];
        yield 'doctrine_batch_writer' => [DoctrineBatchWriterTask::class, 'doctrine_batch_writer', 'cleverage_doctrine_process.task.doctrine_batch_writer'];
        yield 'doctrine_cleaner' => [DoctrineCleanerTask::class, 'doctrine_cleaner', 'cleverage_doctrine_process.task.doctrine_cleaner'];
        yield 'doctrine_detacher' => [DoctrineDetacherTask::class, 'doctrine_detacher', 'cleverage_doctrine_process.task.doctrine_detacher'];
        yield 'doctrine_reader' => [DoctrineReaderTask::class, 'doctrine_reader', 'cleverage_doctrine_process.task.doctrine_reader'];
        yield 'doctrine_refresher' => [DoctrineRefresherTask::class, 'doctrine_refresher', 'cleverage_doctrine_process.task.doctrine_refresher'];
        yield 'doctrine_remover' => [DoctrineRemoverTask::class, 'doctrine_remover', 'cleverage_doctrine_process.task.doctrine_remover'];
        yield 'doctrine_writer' => [DoctrineWriterTask::class, 'doctrine_writer', 'cleverage_doctrine_process.task.doctrine_writer'];
    }

    /**
     * @param class-string $class
     */
    #[DataProvider('provideTasks')]
    public function testTaskIsRegistered(string $class, string $code, string $deprecatedId): void
    {
        $container = new ContainerBuilder();
        (new CleverAgeDoctrineProcessExtension())->load([], $container);

        // The class name is the service id
        $definition = $container->getDefinition($class);
        // Tasks are stateful: each process execution must get its own instance
        self::assertFalse($definition->isShared());
        // Logged in the process tasks channel
        self::assertSame([['channel' => 'cleverage_process_task']], $definition->getTag('monolog.logger'));
        // Referenced by its code in the process configurations (task: <code>)
        self::assertSame([['code' => $code]], $definition->getTag('cleverage.task'));
        // Public for the direct access from the service container, deprecated
        self::assertTrue($definition->isPublic());
        self::assertSame([['package' => 'cleverage/doctrine-process-bundle', 'version' => '3.3']], $definition->getTag('container.private'));

        // Former id, deprecated
        $alias = $container->getAlias($deprecatedId);
        self::assertSame($class, (string) $alias);
        self::assertFalse($alias->isPublic());
        self::assertTrue($alias->isDeprecated());
        self::assertSame(
            ['package' => 'cleverage/doctrine-process-bundle', 'version' => '3.3', 'message' => 'The "'.$deprecatedId.'" service alias is deprecated, use the "'.$code.'" task code or the class name of the task instead. It will be removed in 4.0.'],
            $alias->getDeprecation($deprecatedId)
        );
    }

    /**
     * @return iterable<string, array{class-string}>
     */
    public static function provideLoggedTasks(): iterable
    {
        yield 'database_reader' => [DatabaseReaderTask::class];
        yield 'database_updater' => [DatabaseUpdaterTask::class];
        yield 'doctrine_reader' => [DoctrineReaderTask::class];
    }

    /**
     * @param class-string $class
     */
    #[DataProvider('provideLoggedTasks')]
    public function testLoggerIsInjected(string $class): void
    {
        $container = new ContainerBuilder();
        (new CleverAgeDoctrineProcessExtension())->load([], $container);

        $references = [];
        foreach ($container->getDefinition($class)->getArguments() as $argument) {
            if ($argument instanceof Reference) {
                $references[] = (string) $argument;
            }
        }
        // The "channel" of the "monolog.logger" tag only applies to a reference to the "logger" service
        self::assertContains('logger', $references);
        self::assertNotContains('monolog.logger', $references);
    }

    public function testEveryTaskIsTested(): void
    {
        $container = new ContainerBuilder();
        (new CleverAgeDoctrineProcessExtension())->load([], $container);

        $ids = array_filter(
            array_keys($container->getDefinitions()),
            static fn (string $id): bool => str_starts_with($id, 'CleverAge\\DoctrineProcessBundle\\Task\\')
        );
        self::assertEqualsCanonicalizing(array_column(iterator_to_array(self::provideTasks()), 0), array_values($ids));

        // Every task has its own code, prefixed by the bridge name
        $codes = array_column(iterator_to_array(self::provideTasks()), 1);
        self::assertSame($codes, array_unique($codes));
        foreach ($codes as $code) {
            self::assertStringStartsWith('doctrine_', $code);
        }

        // Every former id is a deprecated alias
        $aliases = array_filter(
            array_keys($container->getAliases()),
            static fn (string $id): bool => str_starts_with($id, 'cleverage_doctrine_process.task.')
        );
        self::assertEqualsCanonicalizing(array_column(iterator_to_array(self::provideTasks()), 2), array_values($aliases));
    }
}
