DatabaseReaderTask
==================

Writes data to a database.

Task reference
--------------

* **Service**: `CleverAge\DoctrineProcessBundle\Task\Database\DatabaseUpdaterTask`

Accepted inputs
---------------

Input can be used as the query params if needed

Possible outputs
----------------

Iterate on an entity list returned by a sql query.

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
entry:
  service: '@CleverAge\DoctrineProcessBundle\Task\Database\DatabaseUpdaterTask'
  options:
    sql: 'update author set firstname = :firstname, lastname = :lastname'
    input_as_params: false
    params:
      firstname: 'Pascal'
      lastname: 'Dupont'
```