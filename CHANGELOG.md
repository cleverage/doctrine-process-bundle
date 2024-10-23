v2.0
------

## BC breaks

* [#6](https://github.com/cleverage/doctrine-process-bundle/issues/6) Update services according to Symfony best practices. Services should not use autowiring or autoconfiguration. Instead, all services should be defined explicitly.
  Services must be prefixed with the bundle alias instead of using fully qualified class names => `cleverage_doctrine_process`

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
