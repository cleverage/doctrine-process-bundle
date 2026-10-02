DatabaseReaderTask
==================

Reads rows from a database using a raw SQL query (Doctrine DBAL, no entity hydration) and outputs them one by one, or
by pages of `paginate` rows. By default, it selects all the columns of the `table`; a custom `sql` query can be given
instead. Useful for fast exports or when there is no Doctrine entity mapped on the data.

Task reference
--------------

* **Service**: `CleverAge\DoctrineProcessBundle\Task\Database\DatabaseReaderTask`
* **Iterable task**

Accepted inputs
---------------

Input is ignored, unless `input_as_params` is `true`: the input is then used as the query parameters and must be an
`array` (otherwise an `\UnexpectedValueException` is thrown).

Possible outputs
----------------

* `array`: an associative array (column name => value) for each row returned by the query
* `array`: a list of such rows (at most `paginate` rows) if the `paginate` option is set

If the result set is empty, a log is written with the `empty_log_level` level and the task is skipped.

Options
-------

| Code              | Type          | Required | Default   | Description                                                                                                                                                                                                                        |
|-------------------|---------------|:--------:|-----------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `table`           | `string`      |          |           | Table to read from: the query is `SELECT tbl.* FROM <table> tbl`.<br/>Required when `sql` is not set (ignored otherwise)                                                                                                           |
| `connection`      | `string\|null` |          | `null`    | Name of the Doctrine DBAL connection (as defined in `doctrine.dbal.connections`). If `null`, the default connection is used                                                                                                        |
| `sql`             | `string\|null` |          | `null`    | Custom SQL query to execute, with optional named (`:name`) or positional (`?`) parameters                                                                                                                                          |
| `limit`           | `int\|null`    |          | `null`    | Maximum number of rows. Only used when `sql` is not set                                                                                                                                                                            |
| `offset`          | `int\|null`    |          | `null`    | Index of the first row. Only used when `sql` is not set                                                                                                                                                                            |
| `paginate`        | `int\|null`    |          | `null`    | If set, rows are output by lists of `paginate` rows instead of one by one                                                                                                                                                          |
| `input_as_params` | `bool`        |          | `false`   | Use the input as query parameters instead of the `params` option                                                                                                                                                                   |
| `params`          | `array`       |          | `[]`      | Query parameters (ignored if `input_as_params` is `true`)                                                                                                                                                                          |
| `types`           | `array`       |          | `[]`      | Query parameter types, indexed like the parameters (see [DBAL parameter types](https://www.doctrine-project.org/projects/doctrine-dbal/en/current/reference/data-retrieval-and-manipulation.html#list-of-parameters-conversion)) |
| `empty_log_level` | `string`      |          | `warning` | PSR log level (`Psr\Log\LogLevel` values) used to log an empty result set                                                                                                                                                          |

Examples
--------

* Read all the rows of a table

```yaml
# Task configuration level
read_books:
  service: '@CleverAge\DoctrineProcessBundle\Task\Database\DatabaseReaderTask'
  options:
    table: 'book'
    limit: 100
    offset: 10
    empty_log_level: debug
  outputs: [next_task]
```

* Use a custom query with parameters, and a value coming from the process context
  (`bin/console cleverage:process:execute <process_code> -c lastname:"'King'"`)

```yaml
# Task configuration level
read_books:
  service: '@CleverAge\DoctrineProcessBundle\Task\Database\DatabaseReaderTask'
  options:
    sql: >
      SELECT b.id, b.title, a.lastname AS author
      FROM book b INNER JOIN author a ON a.id = b.author_id
      WHERE a.lastname = :lastname
    params:
      lastname: '{{ lastname }}'
  outputs: [next_task]
```

* Output rows by pages of 500, using the parameters given by the previous task

```yaml
# Task configuration level
get_params:
  service: '@CleverAge\ProcessBundle\Task\ConstantOutputTask'
  options:
    output:
      min_id: 1000
  outputs: [read_books]
read_books:
  service: '@CleverAge\DoctrineProcessBundle\Task\Database\DatabaseReaderTask'
  options:
    sql: 'SELECT * FROM book WHERE id >= :min_id'
    input_as_params: true
    types:
      min_id: 'integer'
    paginate: 500
  outputs: [next_task]
```

Notes
-----

* Options are resolved once per task instance: values coming from the context (`{{ key }}`) are replaced when the
  query is first executed.
* The query is executed on the first execution of the task, then rows are fetched from the database one at a time
  while the process iterates, so memory usage stays low even with big result sets. The DBAL result is freed when the
  process is finalized.
* Array parameters (e.g. for an `IN (:ids)` clause) require an `ArrayParameterType` in `types`, for instance
  `ids: !php/enum Doctrine\DBAL\ArrayParameterType::INTEGER` with Doctrine DBAL 4.
* The query is executed again for each input received by the task (e.g. after an iterable task): with
  `input_as_params`, each input gives its own parameters.
