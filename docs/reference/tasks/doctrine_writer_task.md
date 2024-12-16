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
entry:
  service: '@CleverAge\ProcessBundle\Task\ConstantOutputTask'
  options:
    output:
      firstname: Isaac
      lastname: Asimov
  outputs: [denormalize]
  denormalize:
    service: '@CleverAge\ProcessBundle\Task\Serialization\DenormalizerTask'
    options:
      class: App\Entity\Author
    outputs: [save]
  save:
    service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineWriterTask'
    outputs: [fetch]
  fetch:
    service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineReaderTask'
    options:
      class_name: 'App\Entity\Author'
      criteria:
        lastname: 'Asimov'
```