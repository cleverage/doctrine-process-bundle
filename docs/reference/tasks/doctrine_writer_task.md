DoctrineWriterTask
==================

Write a Doctrine entity to the database.

Task reference
--------------

* **Service**: `CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineWriterTask`

Accepted inputs
---------------

`object`: Doctrine managed entity

Possible outputs
----------------

`object`: Re-outputs given entity

Options
-------

`None`

Example
-------

```yaml
# Task configuration level
code:
  service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineWriterTask'
```