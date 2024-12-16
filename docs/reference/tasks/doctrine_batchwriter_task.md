DoctrineBatchWriterTask
=======================

Writes multiple entities to a database.

Task reference
--------------

* **Service**: `CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineBatchWriterTask`

Accepted inputs
---------------

`array`: Entities to be persisted in the database

Possible outputs
----------------

`array`: Batch of the entities persisted to the database

Options
-------

| Code          | Type  | Required | Default | Description |
|---------------|-------|:--------:|---------|-------------|
| `batch_count` | `int` |          | `10`    | Batch size  |

Example
-------

```yaml
# Task configuration level
code:
  service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineBatchWriterTask'
  options:
    batch_count: 2
```