DoctrineWriterTask
==================

Persists the entity received as input and immediately flushes its entity manager, then outputs the entity. Suited for
low volumes; prefer the [DoctrineBatchWriterTask](doctrine_batchwriter_task.md) for big imports.

Task reference
--------------

* **Service**: `CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineWriterTask`
* **Code**: `doctrine_writer` (`task: doctrine_writer`)

Accepted inputs
---------------

`object`: a Doctrine entity (new or already managed). A `null` input throws a `\RuntimeException`, and an object whose
class is not managed by any entity manager throws an `\UnexpectedValueException`.

Possible outputs
----------------

`object`: the persisted entity (with its generated identifier, if any).

Options
-------

| Code             | Type           | Required | Default | Description                                                                                                                      |
|------------------|----------------|:--------:|---------|----------------------------------------------------------------------------------------------------------------------------------|
| `entity_manager` | `string\|null` |          | `null`  | Name of the entity manager (as defined in `doctrine.orm.entity_managers`). If `null`, the one managing the input's class is used |

Examples
--------

* Create an entity from an array, then save it

```yaml
# Task configuration level
entry:
  task: constant_output
  options:
    output:
      firstname: Isaac
      lastname: Asimov
  outputs: [denormalize]
denormalize:
  task: denormalizer
  options:
    class: App\Entity\Author
  outputs: [save]
save:
  task: doctrine_writer
  outputs: [next_task]
```

Notes
-----

* `flush()` writes **all** the pending changes of the entity manager, not only the input entity.
