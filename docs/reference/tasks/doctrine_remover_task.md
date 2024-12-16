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
entry:
  service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineReaderTask'
  options:
    class_name: 'App\Entity\Book'
    criteria:
      title: 'Dracula'
  outputs: [dump]
  dump:
    service: '@CleverAge\ProcessBundle\Task\Debug\DebugTask'
    outputs: [remover]
  remover:
    service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineRemoverTask'
```