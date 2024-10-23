v2.0
------

## BC breaks

* [#6](https://github.com/cleverage/doctrine-process-bundle/issues/6) Update services according to Symfony best practices. Services should not use autowiring or autoconfiguration. Instead, all services should be defined explicitly.
  Services must be prefixed with the bundle alias instead of using fully qualified class names => `cleverage_doctrine_process`
* [#5](https://github.com/cleverage/doctrine-process-bundle/issues/5) Bump "doctrine/doctrine-bundle": "^2.5" according to Symfony versions supported by `cleverage/process-bundle`
* [#4](https://github.com/cleverage/doctrine-process-bundle/issues/4) Allow installing "doctrine/orm": ^3.0 using at least require "doctrine/orm": "^2.9 || ^3.0".
Forbid "doctrine/dbal" 4 for now (as on "symfony/orm-pack" - symfony/orm-pack@266bae0#diff-d2ab9925cad7eac58e0ff4cc0d251a937ecf49e4b6bf57f8b95aab76648a9d34R7 ) using "doctrine/dbal": "^2.9 || ^3.0".
Add "doctrine/common": "^3.0" and "doctrine/doctrine-migrations-bundle": "^3.2"


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
