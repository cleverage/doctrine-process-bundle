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
# Task configuration level
code:
  service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineWriterTask'
```