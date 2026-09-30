ClearEntityManagerTask
======================

Clears an entity manager: all its managed entities are detached, which frees memory during long imports or exports.

Task reference
--------------

* **Service**: `CleverAge\DoctrineProcessBundle\Task\EntityManager\ClearEntityManagerTask`

Accepted inputs
---------------

Input is ignored.

Possible outputs
----------------

No output is set.

Options
-------

| Code             | Type           | Required | Default | Description                                                                                                                    |
|------------------|----------------|:--------:|---------|--------------------------------------------------------------------------------------------------------------------------------|
| `entity_manager` | `string\|null` |          | `null`  | Name of the entity manager to clear (as defined in `doctrine.orm.entity_managers`). If `null`, the default one is cleared |

Examples
--------

* Clear the default entity manager after each written batch

```yaml
# Task configuration level
batch_write:
  service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineBatchWriterTask'
  options:
    batch_count: 100
  outputs: [clear]
clear:
  service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\ClearEntityManagerTask'
```

* Clear a specific entity manager

```yaml
# Task configuration level
clear:
  service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\ClearEntityManagerTask'
  options:
    entity_manager: 'customer'
```

Notes
-----

* Pending changes that have not been flushed are lost.
* Entities read before the clear become detached: they must not be modified and written afterwards without being
  fetched again.
