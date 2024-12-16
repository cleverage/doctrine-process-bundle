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
entry:
  service: '@CleverAge\ProcessBundle\Task\ConstantIterableOutputTask'
  options:
    output:
      - author1:
          firstname: Firstname 1
          lastname: Lastname
      - author2:
          firstname: Firstname 2
          lastname: Lastname
      - author3:
          firstname: Firstname 3
          lastname: Lastname
  outputs: [iterate]
  iterate:
    service: '@CleverAge\ProcessBundle\Task\InputIteratorTask'
    outputs: [denormalizer]
  denormalizer:
    service: '@CleverAge\ProcessBundle\Task\Serialization\DenormalizerTask'
    options:
      class: App\Entity\Author
    outputs: [batch_write]
  batch_write:
    service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineBatchWriterTask'
    options:
      batch_count: 2
```