DoctrineWriterTask
==================

Write a Doctrine entity to the database.

Task reference
--------------

* **Service**: `CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineWriterTask`

Accepted inputs
---------------

Any doctrine managed entity.

Possible outputs
----------------

Re-output given entity.

Options
-------

None

Example
-------

```yaml
write:
  service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineWriterTask'
```