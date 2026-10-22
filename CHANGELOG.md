Latest
------

v3.3.1
------

### Fixes
* [GHSA-hrcr-p9pc-8vfr](https://github.com/cleverage/doctrine-process-bundle/security/advisories/GHSA-hrcr-p9pc-8vfr) `AbstractDoctrineQueryTask` (`doctrine_reader`): the `order_by` keys must be a field name or path (letters, digits, `_` and `.`) and its directions `asc` or `desc` (case-insensitive) or `null`, otherwise an `UnexpectedValueException` is thrown: they were inserted as is in the DQL, which allowed DQL injection when `order_by` comes from the context or the input.

v3.3
------

### Changes
* Require `cleverage/process-bundle` `^5.4`.
* Please follow [UPGRADE.md v3.3](UPGRADE.md#v33)
* [#66](https://github.com/cleverage/doctrine-process-bundle/issues/66) The configuration key of the bundle (its extension alias) is `cleverage_doctrine_process`, instead of `clever_age_doctrine_process` (no option could be configured there). Configuring a key under `cleverage_doctrine_process` is deprecated: the bundle has no configuration and the keys were ignored without notice (new internal `Configuration`, empty; notice when the container is compiled). They will be rejected in 4.0. Require `symfony/deprecation-contracts`, used directly. Update documentation, add tests.
* [#64](https://github.com/cleverage/doctrine-process-bundle/issues/64) The bundle throws exceptions implementing `ProcessExceptionInterface` of the process bundle instead of raw SPL exceptions (as [cleverage/process-bundle#317](https://github.com/cleverage/process-bundle/issues/317)): new `Exception\UnexpectedValueException` and `Exception\RuntimeException`, extending the SPL exceptions thrown before (same messages, the existing `catch` keep working). Add tests.
* [#62](https://github.com/cleverage/doctrine-process-bundle/issues/62) Composer: require PHP `^8.2` instead of `>=8.2` (PHP 9 is no longer accepted before being tested), remove the authorization of the `symfony/flex` plugin, which is not required (the CI installs it globally), as [cleverage/process-bundle#319](https://github.com/cleverage/process-bundle/issues/319).
* [#58](https://github.com/cleverage/doctrine-process-bundle/issues/58) `DoctrineReaderTask` (`order_by` option): pass the `\SortDirection` enum to `QueryBuilder::addOrderBy()` when doctrine/orm accepts it (3.7 or later, where a string direction is deprecated), the string otherwise (same query). Require `psr/log` `^2 || ^3`: process-bundle does not work with psr/log 1. The tests fail on deprecations and boot a kernel with process-bundle and DoctrineBundle.
* [#57](https://github.com/cleverage/doctrine-process-bundle/issues/57) Define the public API (Symfony BC promise, as [cleverage/process-bundle#279](https://github.com/cleverage/process-bundle/issues/279)): the concrete tasks (`DatabaseReaderTask`, `DatabaseUpdaterTask`, `DoctrineReaderTask`, `DoctrineWriterTask`, `DoctrineBatchWriterTask`, `DoctrineCleanerTask`, `DoctrineDetacherTask`, `DoctrineRefresherTask`, `DoctrineRemoverTask`, `ClearEntityManagerTask`) and `CleverAgeDoctrineProcessBundle` are marked `@final since 3.3`, they will be `final` in 4.0: extending them triggers a deprecation with the Symfony `DebugClassLoader`. `AbstractDoctrineTask` and `AbstractDoctrineQueryTask` stay open. `CleverAgeDoctrineProcessExtension` is marked `@internal`.
* [#56](https://github.com/cleverage/doctrine-process-bundle/issues/56) `CleverAgeDoctrineProcessExtension::findServices()` loads the service definition files with a glob pattern instead of a `Finder` (same files, as [cleverage/process-bundle#271](https://github.com/cleverage/process-bundle/issues/271)), `symfony/finder` is no longer required. Add tests.
* [#54](https://github.com/cleverage/doctrine-process-bundle/issues/54) `CleverAgeDoctrineProcessExtension` extends `Symfony\Component\DependencyInjection\Extension\Extension` instead of `Symfony\Component\HttpKernel\DependencyInjection\Extension` (internal since Symfony 7.1, to be deprecated in 8.1), as the other bundles. Same behaviour: the removed `addAnnotatedClassesToCompile()` / `getAnnotatedClassesToCompile()` methods (deprecated since Symfony 7.1, removed in 8.0) were not used.
* [#52](https://github.com/cleverage/doctrine-process-bundle/issues/52) Require `symfony/polyfill-php83`, `symfony/polyfill-php84` and `symfony/polyfill-php85` ([cleverage/process-bundle#299](https://github.com/cleverage/process-bundle/issues/299)): the functions of PHP 8.3 to 8.5 can be used while supporting PHP 8.2. Rector targets PHP 8.2 (`withPhpVersion()`, `withPhpSets()` read from `composer.json`) and applies the rules using a newer function when its polyfill is required; no rule nor manual replacement applies to the current code.
* [#48](https://github.com/cleverage/doctrine-process-bundle/issues/48) Give a code to the tasks ([cleverage/process-bundle#91](https://github.com/cleverage/process-bundle/issues/91)): `doctrine_database_reader`, `doctrine_database_updater`, `doctrine_clear_entity_manager`, `doctrine_batch_writer`, `doctrine_cleaner`, `doctrine_detacher`, `doctrine_reader`, `doctrine_refresher`, `doctrine_remover` and `doctrine_writer` (`task: doctrine_reader`, with `cleverage/process-bundle` 5.4 or later, referencing them by `service` being deprecated there). The tasks have their class name as service id; fetching them directly from the service container is deprecated (`container.private`), they will be private in 4.0. The former ids `cleverage_doctrine_process.task.*` are deprecated aliases, removed in 4.0. Update documentation, add tests.
* [#46](https://github.com/cleverage/doctrine-process-bundle/issues/46) Add a `.gitattributes`: the development files (`.docker/`, `.github/`, `tests/`, quality tools and PHPUnit configurations, `Makefile`) are no longer part of the archive installed by Composer (`dist`).
* [#43](https://github.com/cleverage/doctrine-process-bundle/issues/43) Clean up composer.json: `doctrine/doctrine-migrations-bundle`, not used by the bundle, is now only suggested (applications relying on the bundle to install it must require it, as `symfony/orm-pack` does); require `doctrine/dbal`, `doctrine/persistence` and `psr/log`, used directly.

### Fixes
* [#42](https://github.com/cleverage/doctrine-process-bundle/issues/42) Fix DatabaseReaderTask, DatabaseUpdaterTask and DoctrineReaderTask: inject the `logger` service instead of `monolog.logger`, so that they log on the `cleverage_process_task` channel of their `monolog.logger` tag (they logged on the `app` channel, without the process context, and their logs were missing from the process execution logs of the UI). Add tests.

v3.2
------

### Changes
* [#38](https://github.com/cleverage/doctrine-process-bundle/issues/38) Add missing tests: DatabaseUpdaterTask on a SQLite database (options, params, connection), AbstractDoctrineQueryTask query builder (criteria, order, limit, offset), readers `next()` before execution, bundle and DI extension.

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
