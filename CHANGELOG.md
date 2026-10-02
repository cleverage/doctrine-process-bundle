Latest
------

### Fixes
* [#32](https://github.com/cleverage/doctrine-process-bundle/issues/32) Fix EntityManager tasks: use the entity manager given by the `entity_manager` option (it was ignored by every task except ClearEntityManagerTask), the one managing the entity class otherwise. Update documentation, add tests.
* [#33](https://github.com/cleverage/doctrine-process-bundle/issues/33) Fix DatabaseReaderTask and DoctrineReaderTask: execute the query again for each input (the input following a complete iteration was skipped). Update documentation, add tests.
* [#34](https://github.com/cleverage/doctrine-process-bundle/issues/34) Fix DatabaseReaderTask: the `table` option is only required when `sql` is not set. Update documentation, add tests.
* [#35](https://github.com/cleverage/doctrine-process-bundle/issues/35) Fix DoctrineDetacherTask error message on a null input (it named DoctrineWriterTask), and throw an explicit `\RuntimeException` on a null input in DoctrineRemoverTask (a `\TypeError` was triggered). Update documentation, add tests.
* [#36](https://github.com/cleverage/doctrine-process-bundle/issues/36) Fix DoctrineReaderTask: hydrate the entities one at a time while iterating (every entity was hydrated before the first output). Update documentation, add tests.

v3.1
------

### Changes
* [#26](https://github.com/cleverage/doctrine-process-bundle/issues/26) Update quality stack: use Rector `withComposerBased()` sets (removed `SYMFONY_64` / `PHPUNIT_100` sets), declare used Symfony packages and PHPUnit range in composer.json, apply quality tools fixes
* [#28](https://github.com/cleverage/doctrine-process-bundle/issues/28) Add missing documentations: complete reference pages for every Task (inherited options, iterable/flushable behaviours, examples, notes), Database to CSV export and CSV to entities import cookbooks. Harmonize index and task template, fix existing documentation.

### Fixes
* [#30](https://github.com/cleverage/doctrine-process-bundle/issues/30) DatabaseReaderTask no longer drops the row following each full page with the `paginate` option

v3.0
------

### Changes
* [#20](https://github.com/cleverage/doctrine-process-bundle/issues/20) Add support for PHP 8.5 and Symfony 8.* Update phpunit/phpunit to version >10.0 Bump version to cleverage/process-bundle ^5.0

### BC breaks
* [#20](https://github.com/cleverage/doctrine-process-bundle/issues/20) Remove support for PHP 8.1 and Symfony 7.3

v2.1
------

### Changes

* [#18](https://github.com/cleverage/doctrine-process-bundle/issues/18) Upgrade to Symfony 7.3 & PHP 8.4

v2.0.1
------

### Fixes

* [#16](https://github.com/cleverage/doctrine-process-bundle/issues/16) Add missing shared: false on tasks

v2.0
------

## BC breaks

* [#6](https://github.com/cleverage/doctrine-process-bundle/issues/6) Update services according to Symfony best practices. 
Services should not use autowiring or autoconfiguration. Instead, all services should be defined explicitly.
  Services must be prefixed with the bundle alias instead of using fully qualified class names => `cleverage_doctrine_process`
* [#5](https://github.com/cleverage/doctrine-process-bundle/issues/5) Bump "doctrine/doctrine-bundle": "^2.5" according to Symfony versions supported by `cleverage/process-bundle`
* [#4](https://github.com/cleverage/doctrine-process-bundle/issues/4) Allow installing "doctrine/orm": ^3.0 using at least require "doctrine/orm": "^2.9 || ^3.0".
Forbid "doctrine/dbal" 4 for now (as on "symfony/orm-pack" - symfony/orm-pack@266bae0#diff-d2ab9925cad7eac58e0ff4cc0d251a937ecf49e4b6bf57f8b95aab76648a9d34R7 ) using "doctrine/dbal": "^2.9 || ^3.0".
Add "doctrine/common": "^3.0" and "doctrine/doctrine-migrations-bundle": "^3.2"
* [#4](https://github.com/cleverage/doctrine-process-bundle/issues/4) Remove DoctrineWriterTask option `global_flush` 
due to removing [partially flush ability](https://github.com/doctrine/orm/blob/3.0.x/UPGRADE.md#bc-break-removed-ability-to-partially-flushcommit-entity-manager-and-unit-of-work) on `doctrine/orm` 3.*
* [#12](https://github.com/cleverage/doctrine-process-bundle/issues/12) Remove PurgeDoctrineCacheTask


### Changes

* [#3](https://github.com/cleverage/doctrine-process-bundle/issues/3) Add Makefile & .docker for local standalone usage
* [#3](https://github.com/cleverage/doctrine-process-bundle/issues/3) Add rector, phpstan & php-cs-fixer configurations & apply it

### Fixes

v2.0-RC1
------

### Changes

* Miscellaneous changes, show full diff : https://github.com/cleverage/doctrine-process-bundle/compare/v1.0.6...v2.0-RC1

v1.0.6
------

### Changes

* Removing `sidus/base-bundle` dependency

### Fixes

* Fixing services.yaml after refactoring

v1.0.5
------

### Changes

* Fixed dependencies after removing `sidus/base-bundle` from the base process bundle

v1.0.4
------

### Fixes

* Fixed OptionsResolver needing "null" instead of "NULL"
* Fixed backward compatibility break after protected function removal

v1.0.3
------

### Fixes

* Fixing update task and allowing to input params properly to both reader and updater tasks

v1.0.2
------

### Changes

* Add DoctrineRefresherTask

v1.0.1
------

### Changes

* Add "doctrine/doctrine-bundle": "~2.0" dependency

v1.0.0
------

* Initial release
