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
use CleverAge\ProcessBundle\Model\ProcessState;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

#[CoversClass(DatabaseReaderTask::class)]
class DatabaseReaderTaskTest extends TestCase
{
    private LoggerInterface $logger;
    private ManagerRegistry $doctrine;

    protected function setUp(): void
    {
        $this->logger = $this->createStub(LoggerInterface::class);
        $this->doctrine = $this->createStub(ManagerRegistry::class);
    }

    public function testExecute(): void
    {
        $state = $this->createMock(ProcessState::class);
        $options = [
            'table' => 'my_table',
            'sql' => 'SELECT * FROM my_table',
            'limit' => null,
            'offset' => null,
            'paginate' => null,
            'input_as_params' => false,
            'params' => [],
            'types' => [],
            'empty_log_level' => LogLevel::WARNING,
            'connection' => null,
        ];

        $resultData = ['id' => 1, 'name' => 'test'];
        $result = $this->createStub(Result::class);
        $result->method('fetchAssociative')->willReturnOnConsecutiveCalls($resultData, false);

        $connection = $this->createStub(Connection::class);
        $connection->method('executeQuery')->willReturn($result);

        $task = new class($this->logger, $this->doctrine, $options, $connection) extends DatabaseReaderTask {
            /**
             * @param array<string, mixed> $testOptions
             */
            public function __construct(LoggerInterface $logger, ManagerRegistry $doctrine, private readonly array $testOptions, private readonly Connection $testConnection)
            {
                parent::__construct($logger, $doctrine);
            }

            /**
             * @return array<string, mixed>
             */
            protected function getOptions(?ProcessState $state = null): array
            {
                return $this->testOptions;
            }

            protected function getConnection(?ProcessState $state = null): Connection
            {
                return $this->testConnection;
            }
        };

        $state->expects($this->once())->method('setOutput')->with($resultData);

        $task->initialize($state);
        $task->execute($state);
    }

    public function testExecuteWithPagination(): void
    {
        $state = $this->createMock(ProcessState::class);
        $options = [
            'table' => 'my_table',
            'sql' => 'SELECT * FROM my_table',
            'limit' => null,
            'offset' => null,
            'paginate' => 2,
            'input_as_params' => false,
            'params' => [],
            'types' => [],
            'empty_log_level' => LogLevel::WARNING,
            'connection' => null,
        ];

        $resultData1 = ['id' => 1, 'name' => 'test1'];
        $resultData2 = ['id' => 2, 'name' => 'test2'];
        $result = $this->createStub(Result::class);
        $result->method('fetchAssociative')->willReturnOnConsecutiveCalls($resultData1, $resultData2, false);

        $connection = $this->createStub(Connection::class);
        $connection->method('executeQuery')->willReturn($result);

        $task = new class($this->logger, $this->doctrine, $options, $connection) extends DatabaseReaderTask {
            /**
             * @param array<string, mixed> $testOptions
             */
            public function __construct(LoggerInterface $logger, ManagerRegistry $doctrine, private readonly array $testOptions, private readonly Connection $testConnection)
            {
                parent::__construct($logger, $doctrine);
            }

            /**
             * @return array<string, mixed>
             */
            protected function getOptions(?ProcessState $state = null): array
            {
                return $this->testOptions;
            }

            protected function getConnection(?ProcessState $state = null): Connection
            {
                return $this->testConnection;
            }
        };
        $state->expects($this->once())->method('setOutput')->with([$resultData1, $resultData2]);

        $task->initialize($state);
        $task->execute($state);
    }

    public function testFinalize(): void
    {
        $task = new DatabaseReaderTask($this->logger, $this->doctrine);

        $state = $this->createStub(ProcessState::class);

        $result = $this->createMock(Result::class);
        $result->expects($this->once())->method('free');

        $reflection = new \ReflectionClass(DatabaseReaderTask::class);
        $statementProperty = $reflection->getProperty('statement');
        $statementProperty->setValue($task, $result);

        $task->finalize($state);
    }

    public function testExecuteWithEmptyResult(): void
    {
        $logger = $this->createMock(LoggerInterface::class); // Use createMock to assert on logger
        $state = $this->createMock(ProcessState::class);
        $options = [
            'table' => 'my_table',
            'sql' => 'SELECT * FROM my_table',
            'limit' => null,
            'offset' => null,
            'paginate' => null,
            'input_as_params' => false,
            'params' => [],
            'types' => [],
            'empty_log_level' => LogLevel::WARNING,
            'connection' => null,
        ];

        $result = $this->createStub(Result::class);
        $result->method('fetchAssociative')->willReturn(false);

        $connection = $this->createStub(Connection::class);
        $connection->method('executeQuery')->willReturn($result);

        $task = new class($logger, $this->doctrine, $options, $connection) extends DatabaseReaderTask {
            /**
             * @param array<string, mixed> $testOptions
             */
            public function __construct(LoggerInterface $logger, ManagerRegistry $doctrine, private readonly array $testOptions, private readonly Connection $testConnection)
            {
                parent::__construct($logger, $doctrine);
            }

            /**
             * @return array<string, mixed>
             */
            protected function getOptions(?ProcessState $state = null): array
            {
                return $this->testOptions;
            }

            protected function getConnection(?ProcessState $state = null): Connection
            {
                return $this->testConnection;
            }
        };
        $logger->expects($this->once())->method('log')->with(LogLevel::WARNING, 'Empty resultset for query', ['options' => $options]);
        $state->expects($this->once())->method('setSkipped')->with(true);

        $task->initialize($state);
        $task->execute($state);
    }

    public function testExecuteWithInputAsParams(): void
    {
        $state = $this->createMock(ProcessState::class);
        $state->method('getInput')->willReturn(['id' => 1]);

        $options = [
            'table' => 'my_table',
            'sql' => 'SELECT * FROM my_table WHERE id = :id',
            'limit' => null,
            'offset' => null,
            'paginate' => null,
            'input_as_params' => true,
            'params' => [],
            'types' => [],
            'empty_log_level' => LogLevel::WARNING,
            'connection' => null,
        ];

        $resultData = ['id' => 1, 'name' => 'test'];
        $result = $this->createStub(Result::class);
        $result->method('fetchAssociative')->willReturnOnConsecutiveCalls($resultData, false);

        $connection = $this->createMock(Connection::class);
        $connection->expects($this->once())->method('executeQuery')->with($options['sql'], ['id' => 1], $options['types'])->willReturn($result);

        $task = new class($this->logger, $this->doctrine, $options, $connection) extends DatabaseReaderTask {
            /**
             * @param array<string, mixed> $testOptions
             */
            public function __construct(LoggerInterface $logger, ManagerRegistry $doctrine, private readonly array $testOptions, private readonly Connection $testConnection)
            {
                parent::__construct($logger, $doctrine);
            }

            /**
             * @return array<string, mixed>
             */
            protected function getOptions(?ProcessState $state = null): array
            {
                return $this->testOptions;
            }

            protected function getConnection(?ProcessState $state = null): Connection
            {
                return $this->testConnection;
            }
        };
        $state->expects($this->once())->method('setOutput')->with($resultData);

        $task->initialize($state);
        $task->execute($state);
    }

    public function testNext(): void
    {
        $state = $this->createMock(ProcessState::class);
        $options = [
            'table' => 'my_table',
            'sql' => 'SELECT * FROM my_table',
            'limit' => null,
            'offset' => null,
            'paginate' => null,
            'input_as_params' => false,
            'params' => [],
            'types' => [],
            'empty_log_level' => LogLevel::WARNING,
            'connection' => null,
        ];

        $resultData1 = ['id' => 1, 'name' => 'test1'];
        $resultData2 = ['id' => 2, 'name' => 'test2'];
        $result = $this->createStub(Result::class);
        $result->method('fetchAssociative')->willReturnOnConsecutiveCalls($resultData1, $resultData2, false, false);

        $connection = $this->createStub(Connection::class);
        $connection->method('executeQuery')->willReturn($result);

        $task = new class($this->logger, $this->doctrine, $options, $connection) extends DatabaseReaderTask {
            /**
             * @param array<string, mixed> $testOptions
             */
            public function __construct(LoggerInterface $logger, ManagerRegistry $doctrine, private readonly array $testOptions, private readonly Connection $testConnection)
            {
                parent::__construct($logger, $doctrine);
            }

            /**
             * @return array<string, mixed>
             */
            protected function getOptions(?ProcessState $state = null): array
            {
                return $this->testOptions;
            }

            protected function getConnection(?ProcessState $state = null): Connection
            {
                return $this->testConnection;
            }
        };

        $task->initialize($state);

        $state->expects($this->once())->method('setOutput')->with($resultData1);
        $task->execute($state);

        // The statement is now initialized, we can test next()
        $this->assertTrue($task->next($state));
        $this->assertFalse($task->next($state));
    }

    public function testInitializeStatementWithSql(): void
    {
        $state = $this->createStub(ProcessState::class);
        $options = [
            'table' => 'my_table',
            'sql' => 'SELECT * FROM my_table',
            'limit' => null,
            'offset' => null,
            'paginate' => null,
            'input_as_params' => false,
            'params' => [],
            'types' => [],
            'empty_log_level' => LogLevel::WARNING,
            'connection' => null,
        ];

        $connection = $this->createMock(Connection::class);
        $connection->expects($this->once())->method('executeQuery')->with($options['sql'], [], [])->willReturn($this->createStub(Result::class));

        $task = new class($this->logger, $this->doctrine, $options, $connection) extends DatabaseReaderTask {
            /**
             * @param array<string, mixed> $testOptions
             */
            public function __construct(LoggerInterface $logger, ManagerRegistry $doctrine, private readonly array $testOptions, private readonly Connection $testConnection)
            {
                parent::__construct($logger, $doctrine);
            }

            /**
             * @return array<string, mixed>
             */
            protected function getOptions(?ProcessState $state = null): array
            {
                return $this->testOptions;
            }

            protected function getConnection(?ProcessState $state = null): Connection
            {
                return $this->testConnection;
            }
        };

        $this->callInitializeStatement($task, $state);
    }

    public function testInitializeStatementWithoutSql(): void
    {
        $state = $this->createStub(ProcessState::class);
        $options = [
            'table' => 'my_table',
            'sql' => null,
            'limit' => 10,
            'offset' => 5,
            'paginate' => null,
            'input_as_params' => false,
            'params' => [],
            'types' => [],
            'empty_log_level' => LogLevel::WARNING,
            'connection' => null,
        ];

        $qb = $this->createMock(QueryBuilder::class);
        $qb->expects($this->once())->method('select')->with('tbl.*')->willReturnSelf();
        $qb->expects($this->once())->method('from')->with('my_table', 'tbl')->willReturnSelf();
        $qb->expects($this->once())->method('setMaxResults')->with(10)->willReturnSelf();
        $qb->expects($this->once())->method('setFirstResult')->with(5)->willReturnSelf();
        $qb->expects($this->once())->method('getSQL')->willReturn('SELECT tbl.* FROM my_table LIMIT 10 OFFSET 5');

        $connection = $this->createMock(Connection::class);
        $connection->method('createQueryBuilder')->willReturn($qb);
        $connection->expects($this->once())->method('executeQuery')->with('SELECT tbl.* FROM my_table LIMIT 10 OFFSET 5', [], [])->willReturn($this->createStub(Result::class));

        $task = new class($this->logger, $this->doctrine, $options, $connection) extends DatabaseReaderTask {
            /**
             * @param array<string, mixed> $testOptions
             */
            public function __construct(LoggerInterface $logger, ManagerRegistry $doctrine, private readonly array $testOptions, private readonly Connection $testConnection)
            {
                parent::__construct($logger, $doctrine);
            }

            /**
             * @return array<string, mixed>
             */
            protected function getOptions(?ProcessState $state = null): array
            {
                return $this->testOptions;
            }

            protected function getConnection(?ProcessState $state = null): Connection
            {
                return $this->testConnection;
            }
        };

        $this->callInitializeStatement($task, $state);
    }

    public function testInitializeStatementWithInvalidParams(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        $state = $this->createStub(ProcessState::class);
        $state->method('getInput')->willReturn('not an array');

        $options = [
            'table' => 'my_table',
            'sql' => 'SELECT * FROM my_table',
            'limit' => null,
            'offset' => null,
            'paginate' => null,
            'input_as_params' => true,
            'params' => [],
            'types' => [],
            'empty_log_level' => LogLevel::WARNING,
            'connection' => null,
        ];

        $connection = $this->createStub(Connection::class);

        $task = new class($this->logger, $this->doctrine, $options, $connection) extends DatabaseReaderTask {
            /**
             * @param array<string, mixed> $testOptions
             */
            public function __construct(LoggerInterface $logger, ManagerRegistry $doctrine, private readonly array $testOptions, private readonly Connection $testConnection)
            {
                parent::__construct($logger, $doctrine);
            }

            /**
             * @return array<string, mixed>
             */
            protected function getOptions(?ProcessState $state = null): array
            {
                return $this->testOptions;
            }

            protected function getConnection(?ProcessState $state = null): Connection
            {
                return $this->testConnection;
            }
        };

        $this->callInitializeStatement($task, $state);
    }

    private function callInitializeStatement(DatabaseReaderTask $task, ProcessState $state): Result
    {
        $reflection = new \ReflectionClass(DatabaseReaderTask::class);
        $method = $reflection->getMethod('initializeStatement');

        /** @var Result $result */
        $result = $method->invoke($task, $state);

        return $result;
    }
}
