DatabaseReaderTask
==================

Reads data from a Doctrine Repository.

Task reference
--------------

* **Service**: `CleverAge\DoctrineProcessBundle\Task\Database\DatabaseReaderTask`
* **Iterable task**

Accepted inputs
---------------

Input is ignored

Possible outputs
----------------

Iterate on an entity list returned by a Doctrine query.

Options
-------

| Code             | Type | Required | Default | Description                                           |
|------------------| ---- | :------: | ------- |-------------------------------------------------------|
| `table`          | `string` | **X** |  | Table                              |
| `params`       | `array` | | `[]` | List of field => value to use while matching entities |
| `limit`          | `int` or `null` | | `null` | Result max count                                      |
| `offset`         | `int` or `null` | | `null` | Result first item offset                              |
| `entity_manager` | `string` or `null` | | `null` | Use another entity manager than the default           |

Example
-------

https://github.com/cleverage/process-bundle-ui-demo/blob/main/config/packages/process/demo.doctrine.read.yaml
