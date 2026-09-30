DatabaseUpdaterTask
===================

Executes a SQL statement (`INSERT`, `UPDATE`, `DELETE`, ...) on a database using Doctrine DBAL, and outputs the number
of affected rows. By default, the input is used as the statement parameters, so the task can be executed once per
received item.

Task reference
--------------

* **Service**: `CleverAge\DoctrineProcessBundle\Task\Database\DatabaseUpdaterTask`

Accepted inputs
---------------

`array`: the statement parameters, when `input_as_params` is `true` (default). Any other type throws an
`\UnexpectedValueException`.

If `input_as_params` is `false`, input is ignored and the `params` option is used.

Possible outputs
----------------

`int`: number of rows affected by the statement.

Options
-------

| Code              | Type          | Required | Default | Description                                                                                                                                                                                                                        |
|-------------------|---------------|:--------:|---------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `sql`             | `string`      |  **X**   |         | SQL statement to execute, with optional named (`:name`) or positional (`?`) parameters                                                                                                                                             |
| `connection`      | `string\|null` |          | `null`  | Name of the Doctrine DBAL connection (as defined in `doctrine.dbal.connections`). If `null`, the default connection is used                                                                                                        |
| `input_as_params` | `bool`        |          | `true`  | Use the input as statement parameters instead of the `params` option                                                                                                                                                               |
| `params`          | `array`       |          | `[]`    | Statement parameters (ignored if `input_as_params` is `true`)                                                                                                                                                                      |
| `types`           | `array`       |          | `[]`    | Statement parameter types, indexed like the parameters (see [DBAL parameter types](https://www.doctrine-project.org/projects/doctrine-dbal/en/current/reference/data-retrieval-and-manipulation.html#list-of-parameters-conversion)) |

Examples
--------

* Update rows with static parameters, or parameters coming from the process context
  (`bin/console cleverage:process:execute <process_code> -c firstname:"'Stephen'" -c lastname:"'King'"`)

```yaml
# Task configuration level
update_authors:
  service: '@CleverAge\DoctrineProcessBundle\Task\Database\DatabaseUpdaterTask'
  options:
    sql: 'UPDATE author SET firstname = :firstname WHERE lastname = :lastname'
    input_as_params: false
    params:
      firstname: '{{ firstname }}'
      lastname: '{{ lastname }}'
  outputs: [next_task]
```

* Insert each line of a CSV file (the keys of each line must match the statement parameters)

```yaml
# Task configuration level
read:
  service: '@CleverAge\ProcessBundle\Task\File\Csv\CsvReaderTask'
  options:
    file_path: '%kernel.project_dir%/var/data/authors.csv' # Headers: firstname;lastname
  outputs: [insert]
insert:
  service: '@CleverAge\DoctrineProcessBundle\Task\Database\DatabaseUpdaterTask'
  options:
    sql: 'INSERT INTO author (firstname, lastname) VALUES (:firstname, :lastname)'
```

Notes
-----

* Options are resolved once per task instance: values coming from the context (`{{ key }}`) are replaced on the first
  execution.
* Each execution runs the statement immediately, outside any explicit transaction: for big volumes, consider a
  single set-based statement, or the [DoctrineBatchWriterTask](doctrine_batchwriter_task.md) with entities.
