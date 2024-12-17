DoctrineRefresherTask
=====================

Refreshes a Doctrine entity from the entity manager

Task reference
--------------

* **Service**: `CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineRefresherTask`

Accepted inputs
---------------

`object`: Doctrine managed entity

Possible outputs
----------------

`object`: The refreshed entity

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