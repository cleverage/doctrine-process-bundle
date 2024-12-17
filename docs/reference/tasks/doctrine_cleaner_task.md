DoctrineCleanerTask
====================

Clear the entity manager of an entity.

Task reference
--------------

* **Service**: `CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineCleanerTask`

Accepted inputs
---------------

`object`: Entity to be persisted in the database

Possible outputs
----------------

None

Options
-------

None

Example
-------

```yaml
# Task configuration level
code:
  service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineCleanerTask'
```