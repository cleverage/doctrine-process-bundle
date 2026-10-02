DoctrineCleanerTask
===================

Clears the entity manager that manages the class of the entity received as input: **all** the entities of this
entity manager are detached (not only the input entity). Useful when the entity manager is not the default one, as it
is guessed from the input (unless the `entity_manager` option is set).

Task reference
--------------

* **Service**: `CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineCleanerTask`

Accepted inputs
---------------

`object`: a Doctrine entity, used to find its entity manager. A `null` input throws a `\RuntimeException`, and an
object whose class is not managed by any entity manager throws an `\UnexpectedValueException`.

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

* Clear the entity manager after each processed entity

```yaml
# Task configuration level
read_authors:
  service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineReaderTask'
  options:
    class_name: 'App\Entity\Author'
  outputs: [export, clean]
export:
  service: '@CleverAge\ProcessBundle\Task\Debug\DebugTask'
clean:
  service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineCleanerTask'
```

Notes
-----

* Pending changes that have not been flushed are lost.
* To clear an entity manager without any input, or by its name, use the [ClearEntityManagerTask](doctrine_clear_task.md).
