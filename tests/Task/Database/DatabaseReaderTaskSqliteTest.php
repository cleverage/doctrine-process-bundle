<?php

declare(strict_types=1);

/*
 * This file is part of the CleverAge/DoctrineProcessBundle package.
 *
 * Copyright (c) Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\DoctrineProcessBundle\Tests\Task\Database;

use CleverAge\DoctrineProcessBundle\Task\Database\DatabaseReaderTask;
use CleverAge\ProcessBundle\Configuration\ProcessConfiguration;
use CleverAge\ProcessBundle\Configuration\TaskConfiguration;
use CleverAge\ProcessBundle\Context\ContextualOptionResolver;
use CleverAge\ProcessBundle\Model\ProcessHistory;
use CleverAge\ProcessBundle\Model\ProcessState;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

/**
 * DatabaseReaderTask on a real (in-memory SQLite) database, iterating like the process manager does.
 */
#[CoversClass(DatabaseReaderTask::class)]
class DatabaseReaderTaskSqliteTest extends TestCase
{
    private Connection $connection;

    protected function setUp(): void
    {
        $this->connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $this->connection->executeStatement('CREATE TABLE book (id INTEGER PRIMARY KEY, title VARCHAR(255))');
        foreach (['It', 'Salem', 'Fahrenheit 451'] as $i => $title) {
            $this->connection->insert('book', ['id' => $i + 1, 'title' => $title]);
        }
    }

    public function testReadTable(): void
    {
        [$task, $state] = $this->createTask(['table' => 'book']);

        self::assertSame(
            [['id' => 1, 'title' => 'It'], ['id' => 2, 'title' => 'Salem'], ['id' => 3, 'title' => 'Fahrenheit 451']],
            $this->iterate($task, $state, null)
        );
    }

    public function testSqlWithoutTable(): void
    {
        [$task, $state] = $this->createTask(['sql' => 'SELECT title FROM book ORDER BY id']);

        self::assertSame([['title' => 'It'], ['title' => 'Salem'], ['title' => 'Fahrenheit 451']], $this->iterate($task, $state, null));
    }

    public function testTableOrSqlRequired(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage('The option "table" is required when the option "sql" is not set.');
        $this->createTask([]);
    }

    public function testEachInputExecutesTheQueryAgain(): void
    {
        [$task, $state] = $this->createTask(['sql' => 'SELECT title FROM book ORDER BY id']);
        $titles = [['title' => 'It'], ['title' => 'Salem'], ['title' => 'Fahrenheit 451']];

        self::assertSame($titles, $this->iterate($task, $state, 'first'));
        self::assertSame($titles, $this->iterate($task, $state, 'second'));
        self::assertSame($titles, $this->iterate($task, $state, 'third'));
    }

    public function testEachInputExecutesTheQueryAgainWithPagination(): void
    {
        [$task, $state] = $this->createTask(['sql' => 'SELECT id FROM book ORDER BY id', 'paginate' => 2]);
        $pages = [[['id' => 1], ['id' => 2]], [['id' => 3]]];

        self::assertSame($pages, $this->iterate($task, $state, 'first'));
        self::assertSame($pages, $this->iterate($task, $state, 'second'));
    }

    public function testInputAsParams(): void
    {
        [$task, $state] = $this->createTask(['sql' => 'SELECT title FROM book WHERE id = :id', 'input_as_params' => true]);

        self::assertSame([['title' => 'Salem']], $this->iterate($task, $state, ['id' => 2]));
        self::assertSame([['title' => 'It']], $this->iterate($task, $state, ['id' => 1]));
    }

    public function testArrayParameter(): void
    {
        [$task, $state] = $this->createTask([
            'sql' => 'SELECT title FROM book WHERE id IN (:ids) ORDER BY id',
            'params' => ['ids' => [1, 3]],
            'types' => ['ids' => ArrayParameterType::INTEGER],
        ]);

        self::assertSame([['title' => 'It'], ['title' => 'Fahrenheit 451']], $this->iterate($task, $state, null));
    }

    public function testNextBeforeExecute(): void
    {
        [$task, $state] = $this->createTask(['table' => 'book']);

        self::assertFalse($task->next($state));
    }

    public function testConnectionOption(): void
    {
        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects(self::once())->method('getConnection')->with('legacy')->willReturn($this->connection);
        [$task, $state] = $this->createTask(['sql' => 'SELECT id FROM book WHERE id = 1', 'connection' => 'legacy'], $doctrine);

        self::assertSame([['id' => 1]], $this->iterate($task, $state, null));
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array{DatabaseReaderTask, ProcessState}
     */
    private function createTask(array $options, ?ManagerRegistry $doctrine = null): array
    {
        if (!$doctrine instanceof ManagerRegistry) {
            $doctrine = $this->createStub(ManagerRegistry::class);
            $doctrine->method('getConnection')->willReturn($this->connection);
        }

        $processConfiguration = new ProcessConfiguration('test', []);
        $state = new ProcessState($processConfiguration, new ProcessHistory($processConfiguration));
        $state->setContextualOptionResolver(new ContextualOptionResolver());
        $state->setContext([]);
        $state->setTaskConfiguration(new TaskConfiguration('read', DatabaseReaderTask::class, $options));

        $task = new DatabaseReaderTask(new NullLogger(), $doctrine);
        $task->initialize($state);

        return [$task, $state];
    }

    /**
     * Execute the task for one input, then iterate until next() returns false, as the process manager does.
     *
     * @return list<mixed>
     */
    private function iterate(DatabaseReaderTask $task, ProcessState $state, mixed $input): array
    {
        $outputs = [];
        $state->reset(true);
        $state->setInput($input);
        do {
            $state->reset(false);
            $task->execute($state);
            if ($state->isSkipped()) {
                break;
            }
            $outputs[] = $state->getOutput();
        } while ($task->next($state));

        return $outputs;
    }
}
