## Prerequisite

CleverAge/ProcessBundle must be [installed](https://github.com/cleverage/process-bundle/blob/main/docs/01-quick_start.md#installation).

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

This bundle relies on [doctrine/doctrine-bundle](https://github.com/doctrine/DoctrineBundle) and
[doctrine/orm](https://www.doctrine-project.org/projects/orm.html), installed as dependencies: the bundle
`Doctrine\Bundle\DoctrineBundle\DoctrineBundle` must be enabled as well.

## Configuration

This bundle has no configuration of its own: its tasks use the Doctrine connections and entity managers configured in
`config/packages/doctrine.yaml`.

```yaml
# config/packages/doctrine.yaml
doctrine:
    dbal:
        url: '%env(resolve:DATABASE_URL)%'
    orm:
        mappings:
            App:
                type: attribute
                dir: '%kernel.project_dir%/src/Entity'
                prefix: 'App\Entity'
```

* **Database** tasks ([DatabaseReaderTask](reference/tasks/database_reader_task.md),
  [DatabaseUpdaterTask](reference/tasks/database_updater_task.md)) run raw SQL queries through Doctrine DBAL. Their
  `connection` option takes the name of a connection (a key under `doctrine.dbal.connections`); the default connection
  is used if it is not set.
* **EntityManager** tasks work with Doctrine ORM entities. Their `entity_manager` option takes the name of an entity
  manager (a key under `doctrine.orm.entity_managers`); if it is not set, they use the entity manager that manages the
  class of the handled entity (the default entity manager for the
  [ClearEntityManagerTask](reference/tasks/doctrine_clear_task.md)).

See the [DoctrineBundle documentation](https://symfony.com/bundles/DoctrineBundle/current/configuration.html) for the
configuration of multiple connections and entity managers.

Its tasks are registered as services (non-shared) and are used in your processes by their code, prefixed by
`doctrine_` (e.g. `task: doctrine_reader`, requires `cleverage/process-bundle` 5.4 or later, see its
[task definition](https://github.com/cleverage/process-bundle/blob/main/docs/reference/02-task_definition.md)); the
code of each task is given on its reference page. Referencing them by their class name
(`service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineReaderTask'`) is deprecated since
`cleverage/process-bundle` 5.4.

Their service id is their class name. Fetching them directly from the service container is deprecated since 3.3 (they
will be private in 4.0); their former ids, `cleverage_doctrine_process.task.*` (e.g.
`cleverage_doctrine_process.task.doctrine_reader`), are deprecated aliases since 3.3, removed in 4.0.

No option can be configured under `clever_age_doctrine_process`: a key there is ignored. Configuring one is deprecated since 3.3 (an `E_USER_DEPRECATED` error is triggered when the container is compiled), the keys will be rejected in 4.0.

## Documentation

- Cookbooks
    - [Database to CSV export](cookbooks/database_to_csv_export.md)
    - [CSV to entities import](cookbooks/csv_to_entities_import.md)
- Reference
    - Tasks
        - Database
            - [DatabaseReaderTask](reference/tasks/database_reader_task.md)
            - [DatabaseUpdaterTask](reference/tasks/database_updater_task.md)
        - EntityManager
            - [ClearEntityManagerTask](reference/tasks/doctrine_clear_task.md)
            - [DoctrineBatchWriterTask](reference/tasks/doctrine_batchwriter_task.md)
            - [DoctrineCleanerTask](reference/tasks/doctrine_cleaner_task.md)
            - [DoctrineDetacherTask](reference/tasks/doctrine_detacher_task.md)
            - [DoctrineReaderTask](reference/tasks/doctrine_reader_task.md)
            - [DoctrineRefresherTask](reference/tasks/doctrine_refresher_task.md)
            - [DoctrineRemoverTask](reference/tasks/doctrine_remover_task.md)
            - [DoctrineWriterTask](reference/tasks/doctrine_writer_task.md)
- [CleverAge/ProcessBundle documentation](https://github.com/cleverage/process-bundle/blob/main/docs/index.md)
