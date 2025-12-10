# Gemini Code Assistant Context

## Project Overview

This project is a Symfony bundle named `CleverAge/DoctrineProcessBundle`. It is part of the `CleverAge/ProcessBundle` ecosystem and provides Doctrine ORM integration for data processing tasks. The bundle allows developers to create and configure ETL-like processes (Extract, Transform, Load) that can read from and write to a database using Doctrine entities.

The core of the bundle is a set of "tasks" that can be chained together in a process. These tasks include:

*   **Reading:** Fetching entities from the database based on criteria.
*   **Writing:** Creating or updating entities.
*   **Batch Writing:** Writing entities in batches for better performance.
*   **Removing:** Deleting entities.
*   **Cleaning/Detaching:** Managing the Doctrine entity manager's identity map.

The bundle is built for Symfony and integrates with the Doctrine project.

## Building and Running

The project uses Docker for its development environment. The following commands are available in the `Makefile`:

*   **`make start`**: Starts the Docker containers for the development environment.
*   **`make stop`**: Stops the Docker containers.
*   **`make bash`**: Opens a bash shell inside the PHP container.

### Dependencies

PHP dependencies are managed with Composer. To install them, run:

```bash
make src/vendor
```

This is typically done as part of the `make start` or `make up` commands.

### Testing

The project uses PHPUnit for testing. To run the test suite:

```bash
make tests
```
or
```bash
make phpunit
```

### Quality and Linting

The project has a set of quality tools to ensure code standards. The main command is:

```bash
make quality
```

This command runs the following tools:

*   **PHPStan**: Static analysis to find potential bugs.
    ```bash
    make phpstan
    ```
*   **PHP-CS-Fixer**: Enforces a consistent coding style.
    ```bash
    make php-cs-fixer
    ```
*   **Rector**: Provides automated refactoring to improve code quality.
    ```bash
    make rector
    ```

## Development Conventions

### Coding Style

The project follows the official Symfony coding standards, as enforced by the `.php-cs-fixer.dist.php` configuration. It uses the `@Symfony` and `@DoctrineAnnotation` rule sets. All files should include a license header.

### Branching and Commits

While not explicitly defined in the repository, a standard Git flow (e.g., feature branches, pull requests) is expected, as indicated by the presence of a `PULL_REQUEST_TEMPLATE.md`.

### Documentation

The `docs` directory contains user-facing documentation for the bundle and its tasks. When adding a new task, it should be documented in a corresponding `.md` file.
