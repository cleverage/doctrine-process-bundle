DoctrineRefresherTask
=====================

Refreshes the entity received as input from the database, overwriting any unsaved change made to it, then outputs it.

Task reference
--------------

* **Service**: `CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineRefresherTask`
* **Code**: `doctrine_refresher` (`task: doctrine_refresher`)

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
  task: doctrine_reader
  options:
    class_name: 'App\Entity\Author'
    criteria:
      lastname: 'King'
  outputs: [modify]
modify:
  task: property_setter
  options:
    values:
      firstname: 'Gérard'
  outputs: [refresh]
refresh:
  task: doctrine_refresher
  outputs: [next_task]
```
