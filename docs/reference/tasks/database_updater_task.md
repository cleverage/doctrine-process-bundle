DatabaseReaderTask
==================

Writes data to a database.

Task reference
--------------

* **Service**: `CleverAge\DoctrineProcessBundle\Task\Database\DatabaseUpdaterTask`

Accepted inputs
---------------

`array`or `None`: Input can be used as the query params if needed

Possible outputs
----------------

`int`: Number of rows changed by the query.

Options
-------

| Code              | Type               | Required | Default   | Description                                    |
|-------------------|--------------------|:--------:|-----------|------------------------------------------------|
| `connection`      | `string`           |          | `null`    | Doctrine connection (default if not specified) |
| `sql`             | `string`           |  **X**   | `null`    | Query to execute                               |
| `input_as_params` | `bool`             |          | `false`   | Use the input as params                        |
| `params`          | `array`            |          | `[]`      | Query params                                   |
| `types`           | `array`            |          | `[]`      | Query params types                             |

Example
-------

```yaml
# Task configuration level
code:
  service: '@CleverAge\DoctrineProcessBundle\Task\Database\DatabaseUpdaterTask'
  options:
    sql: 'update author set firstname = :firstname, lastname = :lastname'
    input_as_params: false
    params:
      firstname: 'Pascal'
      lastname: 'Dupont'
```