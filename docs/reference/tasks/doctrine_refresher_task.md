DoctrineRefresherTask
=====================

Refreshes a Doctrine entity from the entity manager

Task reference
--------------

* **Service**: `CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineRefresherTask`

Accepted inputs
---------------

Any doctrine managed entity.

Possible outputs
----------------

The refreshed entity

Options
-------

None

Example
-------

```yaml
entry:
  service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineReaderTask'
  options:
    class_name: 'App\Entity\Author'
    criteria:
      lastname: 'King'
    order_by:
      lastname: 'asc'
  outputs: [modify]
modify:
  service: '@CleverAge\ProcessBundle\Task\PropertySetterTask'
  options:
      values:
        firstname: Gérard
  outputs: [refresh]
refresh:
    service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineRefresherTask'
    outputs: [dump_refreshed]
```