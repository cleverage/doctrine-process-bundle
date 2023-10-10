.ONESHELL:
SHELL := /bin/bash

linter: #[Linter]
	vendor/bin/php-cs-fixer fix
phpstan: #[Phpstan]
	vendor/bin/phpstan
