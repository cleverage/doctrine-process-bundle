ClearEntityManagerTask
======================

Clear the entity manager.

Task reference
--------------

* **Service**: `CleverAge\DoctrineProcessBundle\Task\EntityManager\ClearEntityManagerTask`

Accepted inputs
---------------

Ignored

Possible outputs
----------------

None

Options
-------

| Code             | Type               | Required | Default | Description                                 |
|------------------|--------------------|:--------:|---------|---------------------------------------------|
| `entity_manager` | `string` or `null` |          | `null`  | Use another entity manager than the default |



Example
-------

```yaml
clear:
  service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\ClearEntityManagerTask'
```