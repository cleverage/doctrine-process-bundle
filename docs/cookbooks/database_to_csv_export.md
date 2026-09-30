Database to CSV export
======================

This recipe exports rows from a database to a CSV file, using a raw SQL query: rows are fetched one at a time, so the
memory usage stays low even with big tables. The author lastname to export is given in the process context.

```yaml
clever_age_process:
    configurations:
        app.database_to_csv_export:
            description: 'Export the books of an author to a CSV file'
            help: "bin/console cleverage:process:execute app.database_to_csv_export -c lastname:\"'King'\""
            tasks:
                read_books:
                    service: '@CleverAge\DoctrineProcessBundle\Task\Database\DatabaseReaderTask'
                    options:
                        table: 'book' # Required, even if a custom sql query is used
                        sql: >
                            SELECT b.id, b.title, a.firstname, a.lastname
                            FROM book b
                            INNER JOIN author a ON a.id = b.author_id
                            WHERE a.lastname = :lastname
                            ORDER BY b.title
                        params:
                            lastname: '{{ lastname }}'
                        empty_log_level: notice
                    outputs: [format]

                format:
                    service: '@CleverAge\ProcessBundle\Task\TransformerTask'
                    options:
                        transformers:
                            mapping:
                                mapping:
                                    id:
                                        code: '[id]'
                                    title:
                                        code: '[title]'
                                    author:
                                        code: ['[firstname]', '[lastname]']
                                        transformers:
                                            implode:
                                                separator: ' '
                    outputs: [write_csv, count_rows]

                count_rows:
                    service: '@CleverAge\ProcessBundle\Task\Reporting\StatCounterTask'

                write_csv:
                    service: '@CleverAge\ProcessBundle\Task\File\Csv\CsvWriterTask'
                    options:
                        file_path: '%kernel.project_dir%/var/exports/books_{date_time}.csv'
                        headers: [id, title, author]
                    outputs: [log_file]

                log_file:
                    service: '@CleverAge\ProcessBundle\Task\Reporting\LoggerTask'
                    options:
                        level: info
                        message: 'Books exported'
```

How it works:
- [DatabaseReaderTask](../reference/tasks/database_reader_task.md) is iterable: the query is executed once, then each
  row (an associative array) goes through the following tasks before the next one is fetched. The `{{ lastname }}`
  placeholder is replaced by the `lastname` context value and bound as a query parameter (no SQL injection). If no
  book matches, a `notice` is logged and the process ends without writing any file.
- The [TransformerTask](https://github.com/cleverage/process-bundle/blob/main/docs/reference/tasks/transformer_task.md)
  builds the CSV line (see the
  [mapping](https://github.com/cleverage/process-bundle/blob/main/docs/reference/transformers/mapping_transformer.md)
  and [implode](https://github.com/cleverage/process-bundle/blob/main/docs/reference/transformers/implode_transformer.md)
  transformers).
- [StatCounterTask](https://github.com/cleverage/process-bundle/blob/main/docs/reference/tasks/stat_counter_task.md)
  counts the exported lines and logs the total at the end of the process.
- [CsvWriterTask](https://github.com/cleverage/process-bundle/blob/main/docs/reference/tasks/csv_writer_task.md) is
  blocking: it writes each line, and outputs the file path once all the rows have been read, to the
  [LoggerTask](https://github.com/cleverage/process-bundle/blob/main/docs/reference/tasks/logger_task.md).

To export entities instead of raw rows, replace the first task by a
[DoctrineReaderTask](../reference/tasks/doctrine_reader_task.md) and read the values with property paths
(e.g. `code: 'author.lastname'`). Note that the DoctrineReaderTask loads all the matching entities in memory: for
big volumes, prefer the DatabaseReaderTask.
