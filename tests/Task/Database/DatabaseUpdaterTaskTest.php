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
use CleverAge\ProcessBundle\Model\ProcessState;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[CoversClass(DatabaseUpdaterTask::class)]
class DatabaseUpdaterTaskTest extends TestCase
{
    public function testExecute(): void
    {
        $doctrine = $this->createStub(ManagerRegistry::class);
        $logger = $this->createStub(LoggerInterface::class);
        $state = $this->createMock(ProcessState::class);
        $options = [
            'sql' => 'UPDATE my_table SET name = :name WHERE id = :id',
            'input_as_params' => false,
            'params' => ['id' => 1, 'name' => 'test'],
            'types' => [],
            'connection' => null,
        ];

        $connection = $this->createMock(Connection::class);
        $connection->expects($this->once())->method('executeStatement')
            ->with($options['sql'], $options['params'], $options['types'])
            ->willReturn(1);

        $task = new class($doctrine, $logger, $options, $connection) extends DatabaseUpdaterTask {
            /**
             * @param array<string, mixed> $testOptions
             */
            public function __construct(ManagerRegistry $doctrine, LoggerInterface $logger, private readonly array $testOptions, private readonly Connection $testConnection)
            {
                parent::__construct($doctrine, $logger);
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

        $state->expects($this->once())->method('setOutput')->with(1);

        $task->initialize($state);
        $task->execute($state);
    }

    public function testExecuteWithInputAsParams(): void
    {
        $doctrine = $this->createStub(ManagerRegistry::class);
        $logger = $this->createStub(LoggerInterface::class);
        $state = $this->createMock(ProcessState::class);
        $state->method('getInput')->willReturn(['id' => 1, 'name' => 'test']);

        $options = [
            'sql' => 'UPDATE my_table SET name = :name WHERE id = :id',
            'input_as_params' => true,
            'params' => [],
            'types' => [],
            'connection' => null,
        ];

        $connection = $this->createMock(Connection::class);
        $connection->expects($this->once())->method('executeStatement')
            ->with($options['sql'], ['id' => 1, 'name' => 'test'], $options['types'])
            ->willReturn(1);

        $task = new class($doctrine, $logger, $options, $connection) extends DatabaseUpdaterTask {
            /**
             * @param array<string, mixed> $testOptions
             */
            public function __construct(ManagerRegistry $doctrine, LoggerInterface $logger, private readonly array $testOptions, private readonly Connection $testConnection)
            {
                parent::__construct($doctrine, $logger);
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

        $state->expects($this->once())->method('setOutput')->with(1);

        $task->initialize($state);
        $task->execute($state);
    }

    public function testExecuteWithInvalidParams(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        $doctrine = $this->createStub(ManagerRegistry::class);
        $logger = $this->createStub(LoggerInterface::class);
        $state = $this->createStub(ProcessState::class);
        $state->method('getInput')->willReturn('not an array');

        $options = [
            'sql' => 'UPDATE my_table SET name = :name WHERE id = :id',
            'input_as_params' => true,
            'params' => [],
            'types' => [],
            'connection' => null,
        ];

        $connection = $this->createStub(Connection::class);

        $task = new class($doctrine, $logger, $options, $connection) extends DatabaseUpdaterTask {
            /**
             * @param array<string, mixed> $testOptions
             */
            public function __construct(ManagerRegistry $doctrine, LoggerInterface $logger, private readonly array $testOptions, private readonly Connection $testConnection)
            {
                parent::__construct($doctrine, $logger);
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
        $task->execute($state);
    }
}
