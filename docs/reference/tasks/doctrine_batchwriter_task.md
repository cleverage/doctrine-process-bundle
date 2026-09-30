DoctrineBatchWriterTask
=======================

Buffers the entities received as input and, every `batch_count` entities, persists them and flushes their entity
manager(s) in one go. Remaining entities are written when the task is flushed, at the end of the upstream iteration.
This is the recommended way to import a large number of entities.

Task reference
--------------

* **Service**: `CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineBatchWriterTask`
* **Flushable task**

Accepted inputs
---------------

`object`: a Doctrine entity (new or already managed). An object whose class is not managed by any entity manager
throws an `\UnexpectedValueException` when the batch is written.

Possible outputs
----------------

`array`: the list of entities written in the batch (at most `batch_count` entities). The task is skipped when the
input is only buffered, and on flush if there is no remaining entity.

Options
-------

| Code             | Type           | Required | Default | Description                                                                                                  |
|------------------|----------------|:--------:|---------|--------------------------------------------------------------------------------------------------------------|
| `batch_count`    | `int`          |          | `10`    | Number of entities to buffer before writing them to the database                                             |
| `entity_manager` | `string\|null` |          | `null`  | Inherited from the base Doctrine task but not used: the entity manager is the one managing each entity class |

Examples
--------

* Write entities by batches of 100, then clear the entity manager after each batch to free memory

```yaml
# Task configuration level
denormalize:
  service: '@CleverAge\ProcessBundle\Task\Serialization\DenormalizerTask'
  options:
    class: App\Entity\Author
  outputs: [batch_write]
batch_write:
  service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineBatchWriterTask'
  options:
    batch_count: 100
  outputs: [clear]
clear:
  service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\ClearEntityManagerTask'
```

* Get back all the written entities at the end of the process

```yaml
# Task configuration level
batch_write:
  service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineBatchWriterTask'
  options:
    batch_count: 2
  outputs: [aggregate]
aggregate:
  service: '@CleverAge\ProcessBundle\Task\AggregateIterableTask' # Receives one array per batch
  outputs: [next_task]
```

Notes
-----

* The batch can contain entities of different classes: each entity is persisted in the entity manager managing its
  class, then every involved entity manager is flushed.
* `flush()` writes **all** the pending changes of the entity manager, not only the buffered entities.
* To use the output as a flat list of entities, iterate over it with the
  [InputIteratorTask](https://github.com/cleverage/process-bundle/blob/main/docs/reference/tasks/input_iterator_task.md).
