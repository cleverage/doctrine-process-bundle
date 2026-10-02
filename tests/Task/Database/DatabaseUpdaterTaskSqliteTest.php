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

use CleverAge\DoctrineProcessBundle\Task\Database\DatabaseUpdaterTask;
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
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

/**
 * DatabaseUpdaterTask on a real (in-memory SQLite) database.
 */
#[CoversClass(DatabaseUpdaterTask::class)]
class DatabaseUpdaterTaskSqliteTest extends TestCase
{
    private Connection $connection;

    protected function setUp(): void
    {
        $this->connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $this->connection->executeStatement('CREATE TABLE book (id INTEGER PRIMARY KEY, title VARCHAR(255), stock INTEGER)');
        foreach (['It', 'Salem', 'Fahrenheit 451'] as $i => $title) {
            $this->connection->insert('book', ['id' => $i + 1, 'title' => $title, 'stock' => 0]);
        }
    }

    public function testInputAsParamsByDefault(): void
    {
        [$task, $state] = $this->createTask(['sql' => 'UPDATE book SET stock = :stock WHERE id = :id']);

        self::assertSame(1, $this->execute($task, $state, ['stock' => 5, 'id' => 2]));
        self::assertSame(1, $this->execute($task, $state, ['stock' => 7, 'id' => 3]));

        self::assertSame([0, 5, 7], $this->getStocks());
    }

    public function testParamsOption(): void
    {
        [$task, $state] = $this->createTask([
            'sql' => 'UPDATE book SET stock = :stock WHERE id IN (:ids)',
            'input_as_params' => false,
            'params' => ['stock' => 3, 'ids' => [1, 3]],
            'types' => ['ids' => ArrayParameterType::INTEGER],
        ]);

        // The input is ignored, the number of affected rows is output
        self::assertSame(2, $this->execute($task, $state, ['stock' => 9]));

        self::assertSame([3, 0, 3], $this->getStocks());
    }

    public function testNoAffectedRow(): void
    {
        [$task, $state] = $this->createTask(['sql' => 'UPDATE book SET stock = 1 WHERE id = :id']);

        self::assertSame(0, $this->execute($task, $state, ['id' => 42]));
    }

    public function testNonArrayInputIsRejected(): void
    {
        [$task, $state] = $this->createTask(['sql' => 'UPDATE book SET stock = 1']);

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Expecting an array of params');
        $this->execute($task, $state, 'not an array');
    }

    public function testSqlIsRequired(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->createTask([]);
    }

    public function testInvalidOptionType(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->createTask(['sql' => 'UPDATE book SET stock = 1', 'input_as_params' => 'yes']);
    }

    public function testConnectionOption(): void
    {
        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects(self::once())->method('getConnection')->with('legacy')->willReturn($this->connection);
        [$task, $state] = $this->createTask(['sql' => 'UPDATE book SET stock = 1', 'connection' => 'legacy'], $doctrine);

        self::assertSame(3, $this->execute($task, $state, []));
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array{DatabaseUpdaterTask, ProcessState}
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
        $state->setTaskConfiguration(new TaskConfiguration('update', DatabaseUpdaterTask::class, $options));

        $task = new DatabaseUpdaterTask($doctrine, new NullLogger());
        $task->initialize($state);

        return [$task, $state];
    }

    private function execute(DatabaseUpdaterTask $task, ProcessState $state, mixed $input): mixed
    {
        $state->reset(true);
        $state->setInput($input);
        $task->execute($state);

        return $state->getOutput();
    }

    /**
     * @return list<int>
     */
    private function getStocks(): array
    {
        return array_map(
            static fn (mixed $stock): int => is_numeric($stock) ? (int) $stock : -1,
            $this->connection->fetchFirstColumn('SELECT stock FROM book ORDER BY id')
        );
    }
}
