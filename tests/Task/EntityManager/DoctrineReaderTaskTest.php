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
use CleverAge\ProcessBundle\Configuration\ProcessConfiguration;
use CleverAge\ProcessBundle\Configuration\TaskConfiguration;
use CleverAge\ProcessBundle\Context\ContextualOptionResolver;
use CleverAge\ProcessBundle\Model\ProcessHistory;
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
use Psr\Log\NullLogger;

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
            'entity_manager' => null,
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
            /**
             * @param array<string, mixed> $testOptions
             */
            public function __construct(LoggerInterface $logger, ManagerRegistry $doctrine, private readonly array $testOptions)
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
            'entity_manager' => null,
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
            /**
             * @param array<string, mixed> $testOptions
             */
            public function __construct(LoggerInterface $logger, ManagerRegistry $doctrine, private readonly array $testOptions)
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
            'entity_manager' => null,
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
            /**
             * @param array<string, mixed> $testOptions
             */
            public function __construct(LoggerInterface $logger, ManagerRegistry $doctrine, private readonly array $testOptions)
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
        };

        $task->initialize($state);

        $call = 0;
        $state->expects($this->exactly(2))->method('setOutput')
            ->with($this->callback(function ($output) use (&$call, $entity1, $entity2) {
                if (0 === $call) {
                    $this->assertSame($entity1, $output);
                }
                if (1 === $call) {
                    $this->assertSame($entity2, $output);
                }
                ++$call;

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
            'entity_manager' => null,
        ];

        $doctrine->method('getManagerForClass')->willReturn(null);

        $task = new class($logger, $doctrine, $options) extends DoctrineReaderTask {
            /**
             * @param array<string, mixed> $testOptions
             */
            public function __construct(LoggerInterface $logger, ManagerRegistry $doctrine, private readonly array $testOptions)
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
        };

        $task->initialize($state);
        $task->execute($state);
    }

    public function testNextBeforeExecute(): void
    {
        [$task, $state] = $this->createIteratingTask(static function (): \Generator {
            yield (object) ['name' => 'entity1'];
        });

        self::assertFalse($task->next($state));
    }

    public function testEntitiesAreHydratedWhileIterating(): void
    {
        $consumed = 0;
        [$task, $state] = $this->createIteratingTask(static function () use (&$consumed): \Generator {
            foreach (['entity1', 'entity2', 'entity3'] as $entity) {
                ++$consumed;
                yield (object) ['name' => $entity];
            }
        });

        $task->execute($state);

        // Only the first entity has been fetched from the query
        self::assertSame(1, $consumed);
        self::assertTrue($task->next($state));
        self::assertSame(2, $consumed);
    }

    public function testEachInputExecutesTheQueryAgain(): void
    {
        $queries = 0;
        [$task, $state] = $this->createIteratingTask(static function () use (&$queries): \Generator {
            ++$queries;
            yield (object) ['name' => 'entity1'];
            yield (object) ['name' => 'entity2'];
        });

        foreach (['first', 'second', 'third'] as $input) {
            $names = [];
            do {
                $state->reset(false);
                $task->execute($state);
                self::assertFalse($state->isSkipped(), "Input {$input} skipped");
                /** @var object{name: string} $output */
                $output = $state->getOutput();
                $names[] = $output->name;
            } while ($task->next($state));

            self::assertSame(['entity1', 'entity2'], $names);
        }
        self::assertSame(3, $queries);
    }

    /**
     * @param \Closure(): \Generator $results
     *
     * @return array{DoctrineReaderTask, ProcessState}
     */
    private function createIteratingTask(\Closure $results): array
    {
        $query = $this->createStub(Query::class);
        $query->method('toIterable')->willReturnCallback($results);

        $qb = $this->createStub(QueryBuilder::class);
        $qb->method('getQuery')->willReturn($query);

        $repository = $this->createStub(EntityRepository::class);
        $repository->method('createQueryBuilder')->willReturn($qb);

        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repository);

        $doctrine = $this->createStub(ManagerRegistry::class);
        $doctrine->method('getManagerForClass')->willReturn($em);

        $processConfiguration = new ProcessConfiguration('test', []);
        $state = new ProcessState($processConfiguration, new ProcessHistory($processConfiguration));
        $state->setContextualOptionResolver(new ContextualOptionResolver());
        $state->setContext([]);
        $state->setTaskConfiguration(new TaskConfiguration('read', DoctrineReaderTask::class, ['class_name' => 'App\\Entity\\MyEntity']));

        $task = new DoctrineReaderTask(new NullLogger(), $doctrine);
        $task->initialize($state);

        return [$task, $state];
    }
}
