DoctrineBatchWriterTask
=======================

Writes multiple entities to a database.

Task reference
--------------

* **Service**: `CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineBatchWriterTask`

Accepted inputs
---------------

An array of entities

Possible outputs
----------------

The batch of the entities persisted to the database

Options
-------

| Code          | Type  | Required | Default | Description |
|---------------|-------|:--------:|---------|-------------|
| `batch_count` | `int` |          | `10`    | Batch size  |

Example
-------

```yaml
batch_write:
  service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineBatchWriterTask'
  options:
    batch_count: 2
```