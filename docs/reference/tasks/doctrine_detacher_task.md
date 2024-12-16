DoctrineDetacherTask
====================

Detach a Doctrine entity from the entity manager

Task reference
--------------

* **Service**: `CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineDetacherTask`

Accepted inputs
---------------

Any doctrine managed entity.

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
  outputs: [detach]
detach:
  service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineDetacherTask'
```