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

namespace CleverAge\DoctrineProcessBundle\Tests\App;

use CleverAge\DoctrineProcessBundle\CleverAgeDoctrineProcessBundle;
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
use CleverAge\ProcessBundle\CleverAgeProcessBundle;
use CleverAge\ProcessBundle\Registry\ProcessConfigurationRegistry;
use CleverAge\ProcessBundle\Registry\TaskRegistry;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Application used by the integration tests: DoctrineBundle (DBAL, SQLite in memory), process-bundle and this
 * bundle, in a temporary directory of its own (per PHP process).
 */
final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    /**
     * Task code => task class, one task of the "test.doctrine" process per code.
     */
    public const TASKS = [
        'doctrine_database_reader' => DatabaseReaderTask::class,
        'doctrine_database_updater' => DatabaseUpdaterTask::class,
        'doctrine_clear_entity_manager' => ClearEntityManagerTask::class,
        'doctrine_batch_writer' => DoctrineBatchWriterTask::class,
        'doctrine_cleaner' => DoctrineCleanerTask::class,
        'doctrine_detacher' => DoctrineDetacherTask::class,
        'doctrine_reader' => DoctrineReaderTask::class,
        'doctrine_refresher' => DoctrineRefresherTask::class,
        'doctrine_remover' => DoctrineRemoverTask::class,
        'doctrine_writer' => DoctrineWriterTask::class,
    ];

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new DoctrineBundle();
        yield new CleverAgeProcessBundle();
        yield new CleverAgeDoctrineProcessBundle();
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    public function getCacheDir(): string
    {
        return $this->getRunDir().'/cache/'.$this->environment;
    }

    public function getLogDir(): string
    {
        return $this->getRunDir().'/logs';
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'secret' => 'test',
            'test' => true,
            'http_method_override' => false,
            'handle_all_throwables' => true,
            'php_errors' => ['log' => true],
            'validation' => ['email_validation_mode' => 'html5'],
        ]);
        if (Kernel::VERSION_ID >= 70300) {
            // Not setting it is deprecated since Symfony 7.3: its default value changes in 8.0
            $container->extension('framework', ['property_info' => ['with_constructor_extractor' => true]]);
        }
        $container->extension('doctrine', [
            'dbal' => ['url' => 'sqlite:///:memory:'],
        ]);

        $services = $container->services();
        // Tasks referenced by code (task: <code>): process-bundle 5.4 or later
        if (class_exists(TaskRegistry::class)) {
            $tasks = [];
            foreach (array_keys(self::TASKS) as $code) {
                $tasks[$code] = ['task' => $code];
            }
            $container->extension('clever_age_process', [
                'configurations' => [
                    'test.doctrine' => ['tasks' => $tasks],
                ],
            ]);
            $services->alias('test.task_registry', TaskRegistry::class)->public();
            $services->alias('test.process_configuration_registry', ProcessConfigurationRegistry::class)->public();
        }
    }

    private function getRunDir(): string
    {
        return sys_get_temp_dir().'/doctrine_process_bundle_tests_'.getmypid();
    }
}
