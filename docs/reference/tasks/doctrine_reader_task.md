DoctrineReaderTask
==================

Reads Doctrine entity from a repository

Task reference
--------------

* **Service**: `CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineReaderTask`

Accepted inputs
---------------

None.

Possible outputs
----------------

Outputs the result set of the entities.

Options
-------


| Code              | Type               | Required | Default   | Description                                    |
|-------------------|--------------------|:--------:|-----------|------------------------------------------------|
| `class_name`      | `string`           |  **X**   | `null`    | Name of the class (e.g. : 'App\Entity\Author') |
| `criteria`        | `array`            |          | `[]`      | Criteria of the query                          |
| `order_by`        | `array`            |          | `[]`      | Order by of the query                          |
| `limit`           | `int` or `null`    |          | `null`    | Result max count                               |
| `offset`          | `int` or `null`    |          | `null`    | Result first item offset                       |
| `empty_log_level` | `string` or `null` |          | `warning` | Log level if the result set is empty           |


Example
-------

```yaml
entry:
  service: '@CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineReaderTask'
  options:
    class_name: 'App\Entity\Author'
    criteria:
      lastname: 'King'
    order_by:
      lastname: 'asc'
    limit: 5
    offset: 3
```