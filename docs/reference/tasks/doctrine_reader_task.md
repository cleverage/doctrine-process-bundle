DoctrineReaderTask
==================

Queries Doctrine entities of a given class from their repository, with simple criteria, ordering, limit and offset, and
outputs them one by one.

Task reference
--------------

* **Service**: `CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineReaderTask`
* **Iterable task**

Accepted inputs
---------------

Input is ignored.

Possible outputs
----------------

`object`: each entity of class `class_name` matching the query.

If the result set is empty, a log is written with the `empty_log_level` level and the task is skipped.

Options
-------

| Code              | Type          | Required | Default   | Description                                                                                                                                                                                                  |
|-------------------|---------------|:--------:|-----------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `class_name`      | `string`      |  **X**   |           | FQCN of the entity to read (e.g. `App\Entity\Author`)                                                                                                                                                        |
| `criteria`        | `array`       |          | `[]`      | Map of `field: value` conditions, combined with `AND`: a scalar value means `=`, a list of values means `IN (...)` and `null` means `IS NULL`. Field names may only contain letters and digits (`[a-zA-Z0-9]`) |
| `order_by`        | `array`       |          | `[]`      | Map of `field: direction` (`asc` or `desc`)                                                                                                                                                                  |
| `limit`           | `int\|null`    |          | `null`    | Maximum number of entities                                                                                                                                                                                   |
| `offset`          | `int\|null`    |          | `null`    | Index of the first entity                                                                                                                                                                                    |
| `empty_log_level` | `string`      |          | `warning` | PSR log level (`Psr\Log\LogLevel` values) used to log an empty result set                                                                                                                                    |
| `entity_manager`  | `string\|null` |          | `null`    | Name of the entity manager (as defined in `doctrine.orm.entity_managers`). If `null`, the one managing the `class_name` is used                                                                              |

Examples
--------

* Read authors whose lastname is `King`, ordered by firstname

```yaml
# Task configuration level
read_authors:
  service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineReaderTask'
  options:
    class_name: 'App\Entity\Author'
    criteria:
      lastname: 'King'
    order_by:
      firstname: 'asc'
    limit: 5
    offset: 3
  outputs: [next_task]
```

* Use an `IN` condition on an association (entity ids), with a value coming from the process context
  (`bin/console cleverage:process:execute <process_code> -c title:"'Dracula'"`)

```yaml
# Task configuration level
read_books:
  service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineReaderTask'
  options:
    class_name: 'App\Entity\Book'
    criteria:
      title: '{{ title }}'
      author: [1, 2, 3]
    empty_log_level: debug
  outputs: [next_task]
```

Notes
-----

* The query is executed on the first execution of the task, then the entities are hydrated one at a time while the
  process iterates (`Query::toIterable()`). They stay managed by the entity manager: for big volumes, clear it
  downstream (see [ClearEntityManagerTask](doctrine_clear_task.md)) or detach the entities (see
  [DoctrineDetacherTask](doctrine_detacher_task.md)) to keep the memory usage low, or read raw rows with the
  [DatabaseReaderTask](database_reader_task.md).
* Entities stay managed by the entity manager: they can be modified then saved with the
  [DoctrineWriterTask](doctrine_writer_task.md).
* The query is executed again for each input received by the task (e.g. after an iterable task); the input itself
  is not used.
* For more complex queries, extend `CleverAge\DoctrineProcessBundle\Task\EntityManager\AbstractDoctrineQueryTask`
  (which provides the options above and a `getQueryBuilder()` method) or this task.
