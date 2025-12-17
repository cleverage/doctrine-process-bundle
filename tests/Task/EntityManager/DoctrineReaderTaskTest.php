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

namespace CleverAge\DoctrineProcessBundle\Tests\Task\EntityManager;

use CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineReaderTask;
use CleverAge\ProcessBundle\Model\ProcessState;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

#[CoversClass(DoctrineReaderTask::class)]
class DoctrineReaderTaskTest extends TestCase
{
    public function testExecute(): void
    {
        $logger = $this->createStub(LoggerInterface::class);
        $doctrine = $this->createStub(ManagerRegistry::class);
        $state = $this->createMock(ProcessState::class);
        $options = [
            'class_name' => 'App\Entity\MyEntity',
            'criteria' => ['id' => 1],
            'order_by' => ['createdAt' => 'DESC'],
            'limit' => 10,
            'offset' => 0,
            'empty_log_level' => LogLevel::WARNING,
        ];

        $entity = new \stdClass();
        $query = $this->createStub(Query::class);
        $query->method('toIterable')->willReturn(new \ArrayIterator([$entity]));

        $qb = $this->createStub(QueryBuilder::class);
        $qb->method('getQuery')->willReturn($query);
        $qb->method('select')->willReturn($qb);
        $qb->method('from')->willReturn($qb);
        $qb->method('andWhere')->willReturn($qb);
        $qb->method('orderBy')->willReturn($qb);
        $qb->method('setFirstResult')->willReturn($qb);
        $qb->method('setMaxResults')->willReturn($qb);
        $qb->method('setParameter')->willReturn($qb);

        $repository = $this->createStub(EntityRepository::class);
        $repository->method('createQueryBuilder')->willReturn($qb);

        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repository);

        $doctrine->method('getManagerForClass')->willReturn($em);

        $task = new class($logger, $doctrine, $options) extends DoctrineReaderTask {
            private array $testOptions;

            public function __construct(LoggerInterface $logger, ManagerRegistry $doctrine, array $testOptions)
            {
                parent::__construct($logger, $doctrine);
                $this->testOptions = $testOptions;
            }

            protected function getOptions(?ProcessState $state = null): array
            {
                return $this->testOptions;
            }
        };

        $state->expects($this->once())->method('setOutput')->with($entity);
        $state->expects($this->never())->method('setSkipped');

        $task->initialize($state);
        $task->execute($state);
    }

    public function testExecuteEmpty(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $doctrine = $this->createStub(ManagerRegistry::class);
        $state = $this->createMock(ProcessState::class);
        $options = [
            'class_name' => 'App\Entity\MyEntity',
            'criteria' => ['id' => 1],
            'order_by' => [],
            'limit' => null,
            'offset' => null,
            'empty_log_level' => LogLevel::WARNING,
        ];

        $query = $this->createStub(Query::class);
        $query->method('toIterable')->willReturn(new \ArrayIterator([]));

        $qb = $this->createStub(QueryBuilder::class);
        $qb->method('getQuery')->willReturn($query);
        $qb->method('select')->willReturn($qb);
        $qb->method('from')->willReturn($qb);
        $qb->method('andWhere')->willReturn($qb);
        $qb->method('setParameter')->willReturn($qb);

        $repository = $this->createStub(EntityRepository::class);
        $repository->method('createQueryBuilder')->willReturn($qb);

        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repository);

        $doctrine->method('getManagerForClass')->willReturn($em);

        $task = new class($logger, $doctrine, $options) extends DoctrineReaderTask {
            private array $testOptions;

            public function __construct(LoggerInterface $logger, ManagerRegistry $doctrine, array $testOptions)
            {
                parent::__construct($logger, $doctrine);
                $this->testOptions = $testOptions;
            }

            protected function getOptions(?ProcessState $state = null): array
            {
                return $this->testOptions;
            }
        };

        $logger->expects(self::once())->method('log')->with(LogLevel::WARNING, 'Empty resultset for query');
        $state->expects($this->once())->method('setSkipped')->with(true);

        $task->initialize($state);
        $task->execute($state);
    }

    public function testNext(): void
    {
        $logger = $this->createStub(LoggerInterface::class);
        $doctrine = $this->createStub(ManagerRegistry::class);
        $state = $this->createMock(ProcessState::class);
        $options = [
            'class_name' => 'App\Entity\MyEntity',
            'criteria' => [],
            'order_by' => [],
            'limit' => null,
            'offset' => null,
            'empty_log_level' => LogLevel::WARNING,
        ];

        $entity1 = new \stdClass();
        $entity2 = new \stdClass();
        $query = $this->createStub(Query::class);
        $query->method('toIterable')->willReturn(new \ArrayIterator([$entity1, $entity2]));

        $qb = $this->createStub(QueryBuilder::class);
        $qb->method('getQuery')->willReturn($query);
        $qb->method('select')->willReturn($qb);
        $qb->method('from')->willReturn($qb);

        $repository = $this->createStub(EntityRepository::class);
        $repository->method('createQueryBuilder')->willReturn($qb);

        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repository);

        $doctrine->method('getManagerForClass')->willReturn($em);

        $task = new class($logger, $doctrine, $options) extends DoctrineReaderTask {
            private array $testOptions;

            public function __construct(LoggerInterface $logger, ManagerRegistry $doctrine, array $testOptions)
            {
                parent::__construct($logger, $doctrine);
                $this->testOptions = $testOptions;
            }

            protected function getOptions(?ProcessState $state = null): array
            {
                return $this->testOptions;
            }
        };

        $task->initialize($state);

        $call = 0;
        $state->expects($this->exactly(2))->method('setOutput')
            ->with($this->callback(function ($output) use (&$call, $entity1, $entity2) {
                if ($call === 0) {
                    $this->assertSame($entity1, $output);
                }
                if ($call === 1) {
                    $this->assertSame($entity2, $output);
                }
                $call++;
                return true;
            }));

        $task->execute($state);
        $this->assertTrue($task->next($state));
        $task->execute($state);
        $this->assertFalse($task->next($state));
    }

    public function testExecuteThrowsExceptionWhenNoManagerFound(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        $logger = $this->createStub(LoggerInterface::class);
        $doctrine = $this->createStub(ManagerRegistry::class);
        $state = $this->createStub(ProcessState::class);
        $options = [
            'class_name' => 'App\Entity\MyEntity',
            'criteria' => [],
            'order_by' => [],
            'limit' => null,
            'offset' => null,
            'empty_log_level' => LogLevel::WARNING,
        ];

        $doctrine->method('getManagerForClass')->willReturn(null);

        $task = new class($logger, $doctrine, $options) extends DoctrineReaderTask {
            private array $testOptions;

            public function __construct(LoggerInterface $logger, ManagerRegistry $doctrine, array $testOptions)
            {
                parent::__construct($logger, $doctrine);
                $this->testOptions = $testOptions;
            }

            protected function getOptions(?ProcessState $state = null): array
            {
                return $this->testOptions;
            }
        };

        $task->initialize($state);
        $task->execute($state);
    }
}