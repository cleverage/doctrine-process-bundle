DoctrineRefresherTask
=====================

Refreshes the entity received as input from the database, overwriting any unsaved change made to it, then outputs it.

Task reference
--------------

* **Service**: `CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineRefresherTask`

Accepted inputs
---------------

`object`: a Doctrine managed entity. A `null` input throws a `\RuntimeException`, and an object whose class is not
managed by any entity manager throws an `\UnexpectedValueException`. Underlying method is Doctrine
`EntityManager::refresh()`, which fails if the entity is not managed.

Possible outputs
----------------

`object`: the refreshed entity.

Options
-------

| Code             | Type           | Required | Default | Description                                                                                                                      |
|------------------|----------------|:--------:|---------|----------------------------------------------------------------------------------------------------------------------------------|
| `entity_manager` | `string\|null` |          | `null`  | Name of the entity manager (as defined in `doctrine.orm.entity_managers`). If `null`, the one managing the input's class is used |

Examples
--------

* Modify an entity, then discard the change by refreshing it

```yaml
# Task configuration level
read_authors:
  service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineReaderTask'
  options:
    class_name: 'App\Entity\Author'
    criteria:
      lastname: 'King'
  outputs: [modify]
modify:
  service: '@CleverAge\ProcessBundle\Task\PropertySetterTask'
  options:
    values:
      firstname: 'Gérard'
  outputs: [refresh]
refresh:
  service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineRefresherTask'
  outputs: [next_task]
```
