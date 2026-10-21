Upgrade Guide
=============

## v3.3

Unless stated otherwise, the deprecations below trigger a notice (`Since cleverage/doctrine-process-bundle 3.3: ...`)
and will be removed in 4.0.

### Dependencies

- `cleverage/process-bundle` `^5.4` is required: follow its
  [upgrade guide](https://github.com/cleverage/process-bundle/blob/main/UPGRADE.md#v54).
- `doctrine/doctrine-migrations-bundle` is no longer required (suggested): require it in your application if you use
  it.

### Configuration key

The configuration key of the bundle is `cleverage_doctrine_process`: `clever_age_doctrine_process` is no longer
recognized. The bundle has no configuration, so remove the key: configuring one under `cleverage_doctrine_process` is
deprecated (the keys were ignored), it will be rejected in 4.0.

### Task codes and service ids

Reference the tasks by their code instead of their service:

```yaml
tasks:
    read:
        # Before (deprecated)
        service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineReaderTask'
        # After
        task: doctrine_reader
```

| Former service id (deprecated alias)                             | Class                     | Code                            |
|------------------------------------------------------------------|---------------------------|---------------------------------|
| `cleverage_doctrine_process.task.database_reader`                | `DatabaseReaderTask`      | `doctrine_database_reader`      |
| `cleverage_doctrine_process.task.database_updater`               | `DatabaseUpdaterTask`     | `doctrine_database_updater`     |
| `cleverage_doctrine_process.task.doctrine_clear_entity_manager`  | `ClearEntityManagerTask`  | `doctrine_clear_entity_manager` |
| `cleverage_doctrine_process.task.doctrine_batch_writer`          | `DoctrineBatchWriterTask` | `doctrine_batch_writer`         |
| `cleverage_doctrine_process.task.doctrine_cleaner`               | `DoctrineCleanerTask`     | `doctrine_cleaner`              |
| `cleverage_doctrine_process.task.doctrine_detacher`              | `DoctrineDetacherTask`    | `doctrine_detacher`             |
| `cleverage_doctrine_process.task.doctrine_reader`                | `DoctrineReaderTask`      | `doctrine_reader`               |
| `cleverage_doctrine_process.task.doctrine_refresher`             | `DoctrineRefresherTask`   | `doctrine_refresher`            |
| `cleverage_doctrine_process.task.doctrine_remover`               | `DoctrineRemoverTask`     | `doctrine_remover`              |
| `cleverage_doctrine_process.task.doctrine_writer`                | `DoctrineWriterTask`      | `doctrine_writer`               |

The service id of the tasks is their class name. Fetching them from the service container (`$container->get()`) is
deprecated: they will be private in 4.0.

### Final classes

The concrete tasks and `CleverAgeDoctrineProcessBundle` are marked `@final`, they will be `final` in 4.0 (extending
them triggers a deprecation with the Symfony `DebugClassLoader`). `AbstractDoctrineTask` and `AbstractDoctrineQueryTask`
stay open. `CleverAgeDoctrineProcessExtension` is `@internal`.

### Exceptions

The bundle throws exceptions implementing `ProcessExceptionInterface` of the process bundle, extending the SPL
exceptions thrown before: existing `catch` blocks keep working.
