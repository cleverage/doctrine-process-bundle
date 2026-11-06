DoctrineBatchWriterTask
=======================

Buffers the entities received as input and, every `batch_count` entities, persists them and flushes their entity
manager(s) in one go. Remaining entities are written when the task is flushed, at the end of the upstream iteration.
This is the recommended way to import a large number of entities.

Task reference
--------------

* **Service**: `CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineBatchWriterTask`
* **Code**: `doctrine_batch_writer` (`task: doctrine_batch_writer`)
* **Flushable task**

Accepted inputs
---------------

`object`: a Doctrine entity (new or already managed). An object whose class is not managed by any entity manager
throws an `UnexpectedValueException` when the batch is written.

Possible outputs
----------------

`array`: the list of entities written in the batch (at most `batch_count` entities). The task is skipped when the
input is only buffered, and on flush if there is no remaining entity.

Options
-------

| Code             | Type           | Required | Default | Description                                                                                                                             |
|------------------|----------------|:--------:|---------|-----------------------------------------------------------------------------------------------------------------------------------------|
| `batch_count`    | `int`          |          | `10`    | Number of entities to buffer before writing them to the database                                                                        |
| `entity_manager` | `string\|null` |          | `null`  | Name of the entity manager (as defined in `doctrine.orm.entity_managers`). If `null`, the one managing the class of each entity is used |

Examples
--------

* Write entities by batches of 100, then clear the entity manager after each batch to free memory

```yaml
# Task configuration level
denormalize:
  task: denormalizer
  options:
    class: App\Entity\Author
  outputs: [batch_write]
batch_write:
  task: doctrine_batch_writer
  options:
    batch_count: 100
  outputs: [clear]
clear:
  task: doctrine_clear_entity_manager
```

* Get back all the written entities at the end of the process

```yaml
# Task configuration level
batch_write:
  task: doctrine_batch_writer
  options:
    batch_count: 2
  outputs: [aggregate]
aggregate:
  task: aggregate_iterable # Receives one array per batch
  outputs: [next_task]
```

Notes
-----

* The batch can contain entities of different classes: each entity is persisted in the entity manager managing its
  class, then every involved entity manager is flushed.
* **Clear the entity manager after each batch** (as in the first example above, with the
  [`doctrine_clear_entity_manager`](clear_entity_manager_task.md) task). The task does not detach the entities it has
  written, so they stay in the identity map of the entity manager, and every `flush()` computes the changes of **all**
  of them again: the import time grows quadratically with the number of entities (measured with 20,000 rows: 27.6 s
  without clearing, 8.2 s with it; 100,000 rows become unmanageable) and the memory keeps growing.
* `flush()` writes **all** the pending changes of the entity manager, not only the buffered entities.
* When writing a batch fails, the exception is thrown (see the `error_strategy` of the task) and the **whole batch is
  lost**: its entities are removed from the buffer, so that they are not written again with the next batch, and
  detached from their entity manager if it is still open. Doctrine closes the entity manager when the flush fails: the
  next batches then fail too, unless the application resets the entity manager (`ManagerRegistry::resetManager()`).
* To use the output as a flat list of entities, iterate over it with the
  [InputIteratorTask](https://github.com/cleverage/process-bundle/blob/main/docs/reference/tasks/input_iterator_task.md).
