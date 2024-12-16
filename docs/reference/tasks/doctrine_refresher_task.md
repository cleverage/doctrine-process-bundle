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
# Task configuration level
code:
    service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineRefresherTask'
```