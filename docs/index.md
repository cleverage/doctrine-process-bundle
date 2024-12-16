## Prerequisite

CleverAge/ProcessBundle must be [installed](https://github.com/cleverage/process-bundle/blob/main/docs/01-quick_start.md#installation.

## Installation

Make sure Composer is installed globally, as explained in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Open a command console, enter your project directory and install it using composer:

```bash
composer require cleverage/doctrine-process-bundle
```

Remember to add the following line to config/bundles.php (not required if Symfony Flex is used)

```php
CleverAge\DoctrineProcessBundle\CleverAgeDoctrineProcessBundle::class => ['all' => true],
```

## Reference

- Tasks
  - [DatabaseReaderTask](reference/tasks/database_reader_task.md)
  - [DatabaseUpdaterTask](reference/tasks/database_updater_task.md)
  - [ClearEntityManagerTask](reference/tasks/doctrine_clear_task.md))
  - [DoctrineBatchWriterTask](reference/tasks/doctrine_batchwriter_task.md)
  - [DoctrineCleanerTask](reference/tasks/doctrine_cleaner_task.md)
  - [DoctrineDetacherTask](reference/tasks/doctrine_detacher_task.md)
  - [DoctrineReaderTask]
  - [DoctrineRefresherTask](reference/tasks/doctrine_refresher_task.md)
  - [DoctrineRemoverTask](reference/tasks/doctrine_remover_task.md)
  - [DoctrineWriterTask](reference/tasks/doctrine_writer_task.md)
