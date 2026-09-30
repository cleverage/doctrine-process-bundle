CSV to entities import
======================

This recipe imports a CSV file into Doctrine entities: invalid lines are rejected, each valid line is denormalized into
an entity, and entities are written to the database by batches. The entity manager is cleared after each batch to keep
the memory usage stable, whatever the size of the file.

Source file (`var/data/authors.csv`):

```csv
firstname;lastname
Isaac;Asimov
Ursula;Le Guin
Mary;Shelley
```

```yaml
clever_age_process:
    configurations:
        app.csv_to_entities_import:
            description: 'Import authors from a CSV file'
            help: 'bin/console cleverage:process:execute app.csv_to_entities_import'
            tasks:
                read_csv:
                    service: '@CleverAge\ProcessBundle\Task\File\Csv\CsvReaderTask'
                    options:
                        file_path: '%kernel.project_dir%/var/data/authors.csv'
                    outputs: [filter_valid]

                filter_valid:
                    service: '@CleverAge\ProcessBundle\Task\FilterTask'
                    options:
                        not_empty:
                            '[firstname]': ~
                            '[lastname]': ~
                    outputs: [denormalize]
                    error_outputs: [log_rejected]

                log_rejected:
                    service: '@CleverAge\ProcessBundle\Task\Reporting\LoggerTask'
                    options:
                        level: warning
                        message: 'Invalid author line'

                denormalize:
                    service: '@CleverAge\ProcessBundle\Task\Serialization\DenormalizerTask'
                    options:
                        class: App\Entity\Author
                    outputs: [batch_write]

                batch_write:
                    service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineBatchWriterTask'
                    options:
                        batch_count: 200
                    outputs: [clear, count_batches]

                count_batches:
                    service: '@CleverAge\ProcessBundle\Task\Reporting\StatCounterTask'

                clear:
                    service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\ClearEntityManagerTask'
```

How it works:
- [CsvReaderTask](https://github.com/cleverage/process-bundle/blob/main/docs/reference/tasks/csv_reader_task.md) is
  iterable: each line (an associative array indexed by the headers) goes through the following tasks before the next
  one is read.
- [FilterTask](https://github.com/cleverage/process-bundle/blob/main/docs/reference/tasks/filter_task.md) sends lines
  with an empty `firstname` or `lastname` to its error branch, where the
  [LoggerTask](https://github.com/cleverage/process-bundle/blob/main/docs/reference/tasks/logger_task.md) logs them.
- [DenormalizerTask](https://github.com/cleverage/process-bundle/blob/main/docs/reference/tasks/denormalizer_task.md)
  creates a new `App\Entity\Author` from each line, using the Symfony Serializer.
- [DoctrineBatchWriterTask](../reference/tasks/doctrine_batchwriter_task.md) buffers the entities and skips its
  outputs until 200 entities are buffered; it then persists and flushes them in one go and outputs the batch. The last
  incomplete batch is written when the task is flushed, at the end of the file.
- [ClearEntityManagerTask](../reference/tasks/doctrine_clear_task.md) is only executed after each written batch: it
  detaches the written entities so that they can be garbage collected.
- [StatCounterTask](https://github.com/cleverage/process-bundle/blob/main/docs/reference/tasks/stat_counter_task.md)
  counts the written batches and logs the total at the end of the process.

Going further:
- To update existing entities instead of always creating new ones, fetch them first (e.g. with a custom task or a
  transformer), or write raw rows with a SQL upsert statement and the
  [DatabaseUpdaterTask](../reference/tasks/database_updater_task.md).
- To validate entities against their mapping constraints before writing them, insert a
  [ValidatorTask](https://github.com/cleverage/process-bundle/blob/main/docs/reference/tasks/validator_task.md) between
  the denormalization and the batch writer.
- Entities with associations (e.g. `App\Entity\Book` and its `author`) must reference managed entities: as the entity
  manager is cleared after each batch, fetch the related entities again for each line rather than caching them.
