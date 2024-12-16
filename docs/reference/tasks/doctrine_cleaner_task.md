DoctrineCleanerTask
====================

Clear the entity manager of an entity.

Task reference
--------------

* **Service**: `CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineCleanerTask`

Accepted inputs
---------------

A doctrine entity

Possible outputs
----------------

None

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
  outputs: [clean]

  clean:
    service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineCleanerTask'
```