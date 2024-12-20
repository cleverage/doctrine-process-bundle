DatabaseReaderTask
==================

Reads data from a database.

Task reference
--------------

* **Service**: `CleverAge\DoctrineProcessBundle\Task\Database\DatabaseReaderTask`
* **Iterable task**

Accepted inputs
---------------

`array`or `None`: Input can be used as the query params if needed

Possible outputs
----------------

`array`: Rows returned by the query.

Options
-------

| Code              | Type               | Required | Default   | Description                                                               |
|-------------------|--------------------|:--------:|-----------|---------------------------------------------------------------------------|
| `connection`      | `string`           |          | `null`    | Doctrine connection (default if not specified)                            |
| `table`           | `string`           |  **X**   | `[]`      | Table of the query                                                        |
| `sql`             | `string`           |          | `null`    | Query to execute (if not specified then: "select tbl.* from `table` tbl") |
| `limit`           | `int` or `null`    |          | `null`    | Result max count                                                          |
| `offset`          | `int` or `null`    |          | `null`    | Result first item offset                                                  |
| `paginate`        | `int` or `null`    |          | `null`    | Paginate the results                                                      |
| `input_as_params` | `bool`             |          | `false`   | Use the input as params                                                   |
| `params`          | `array`            |          | `[]`      | Query params                                                              |
| `types`           | `array`            |          | `[]`      | Query params types                                                        |
| `empty_log_level` | `string` or `null` |          | `warning` | Log level if the result set is empty                                      |


Example
-------

```yaml
# Task configuration level
code:
  service: '@CleverAge\DoctrineProcessBundle\Task\Database\DatabaseReaderTask'
  options:
    table: 'book'
    limit: 10
    offset: 3
    params:
        title: "IT"
    empty_log_level: debug
```