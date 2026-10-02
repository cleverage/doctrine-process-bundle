DoctrineRemoverTask
===================

Removes the entity received as input and immediately flushes its entity manager, deleting it from the database.

Task reference
--------------

* **Service**: `CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineRemoverTask`

Accepted inputs
---------------

`object`: a Doctrine managed entity. A `null` input throws a `\RuntimeException`, and an object whose class is not
managed by any entity manager throws an `\UnexpectedValueException`.

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

* Delete the books having a given title

```yaml
# Task configuration level
read_books:
  service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineReaderTask'
  options:
    class_name: 'App\Entity\Book'
    criteria:
      title: 'Dracula'
  outputs: [remove]
remove:
  service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineRemoverTask'
```

Notes
-----

* `flush()` writes **all** the pending changes of the entity manager, not only the removal.
* Cascade and `orphanRemoval` rules of the entity mapping apply. To delete many rows at once, a single `DELETE`
  statement with the [DatabaseUpdaterTask](database_updater_task.md) is much faster.
