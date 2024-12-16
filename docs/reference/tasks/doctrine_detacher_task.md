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
detach:
  service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineDetacherTask'
```