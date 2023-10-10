.ONESHELL:
SHELL := /bin/bash

linter: #[Linter]
	vendor/bin/php-cs-fixer fix src
	vendor/bin/rector process
	vendor/bin/phpstan