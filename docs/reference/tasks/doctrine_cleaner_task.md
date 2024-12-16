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
# Task configuration level
code:
  service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineCleanerTask'
```