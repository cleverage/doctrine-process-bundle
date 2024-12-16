DoctrineRemoverTask
===================

Removes a Doctrine entity from the entity manager then flushes

Task reference
--------------

* **Service**: `CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineRemoverTask`

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
remover:
  service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineRemoverTask'
```