DoctrineDetacherTask
====================

Detaches the entity received as input from its entity manager: its changes will no longer be tracked nor written by
a subsequent `flush()`, and it can be garbage collected.

Task reference
--------------

* **Service**: `CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineDetacherTask`

Accepted inputs
---------------

`object`: a Doctrine entity. A `null` input throws a `\RuntimeException`, and an object whose class is not managed by
any entity manager throws an `\UnexpectedValueException`. Underlying method is Doctrine `EntityManager::detach()`.

Possible outputs
----------------

No output is set.

Options
-------

| Code             | Type           | Required | Default | Description                                                                                                                      |
|------------------|----------------|:--------:|---------|----------------------------------------------------------------------------------------------------------------------------------|
| `entity_manager` | `string\|null` |          | `null`  | Name of the entity manager (as defined in `doctrine.orm.entity_managers`). If `null`, the one managing the input's class is used |

Examples
--------

* Detach each entity once it has been exported

```yaml
# Task configuration level
read_authors:
  service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineReaderTask'
  options:
    class_name: 'App\Entity\Author'
  outputs: [export, detach]
export:
  service: '@CleverAge\ProcessBundle\Task\Debug\DebugTask'
detach:
  service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineDetacherTask'
```

Notes
-----

* Only the input entity is detached (and its associations configured with `cascade: [detach]`); to detach all the
  entities at once, use the [ClearEntityManagerTask](doctrine_clear_task.md) or the
  [DoctrineCleanerTask](doctrine_cleaner_task.md).
