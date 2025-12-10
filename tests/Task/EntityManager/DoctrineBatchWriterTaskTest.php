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

use CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineBatchWriterTask;
use CleverAge\ProcessBundle\Model\ProcessState;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DoctrineBatchWriterTask::class)]
class DoctrineBatchWriterTaskTest extends TestCase
{
    /**
     * @param array $options
     * @return DoctrineBatchWriterTask
     */
    protected function getTask(array $options = [], ?ManagerRegistry $managerRegistry = null): DoctrineBatchWriterTask
    {
        if (!$managerRegistry) {
            $managerRegistry = $this->createStub(ManagerRegistry::class);
        }
        $task = new DoctrineBatchWriterTask($managerRegistry);

        $state = $this->createStub(ProcessState::class);
        $state->method('getContextualizedOptions')->willReturn($options);
        $task->initialize($state);

        return $task;
    }

    public function testExecuteAddsEntityToBatchAndSkipsWhenBatchCountNotReached(): void
    {
        $entity1 = new \stdClass();
        $state = $this->createMock(ProcessState::class);
        $state->method('getInput')->willReturn($entity1);

        $task = $this->getTask(['batch_count' => 2]);

        $state->expects($this->once())->method('setSkipped')->with(true);
        $task->execute($state);

        $reflection = new \ReflectionClass(DoctrineBatchWriterTask::class);
        $batchProperty = $reflection->getProperty('batch');
        $this->assertCount(1, $batchProperty->getValue($task));
        $this->assertContains($entity1, $batchProperty->getValue($task));
    }

    public function testExecuteFlushesBatchWhenBatchCountReached(): void
    {
        $entity1 = new \stdClass();
        $entity2 = new \stdClass();

        $state = $this->createMock(ProcessState::class);
        $state->method('getInput')->willReturnOnConsecutiveCalls($entity1, $entity2);
        $state->expects($this->once())->method('setOutput')->with([$entity1, $entity2]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->exactly(2))->method('persist'); // Called for entity1 and entity2
        $entityManager->expects($this->once())->method('flush');

        $managerRegistry = $this->createStub(ManagerRegistry::class);
        $managerRegistry->method('getManagerForClass')->willReturn($entityManager);

        $task = $this->getTask(['batch_count' => 2], $managerRegistry);


        // Simulate filling the batch
        $reflection = new \ReflectionClass(DoctrineBatchWriterTask::class);
        $batchProperty = $reflection->getProperty('batch');
        // Add first entity
        $task->execute($state); // batch has 1 entity, not flushed
        $this->assertCount(1, $batchProperty->getValue($task));
        $this->assertContains($entity1, $batchProperty->getValue($task));

        // Add second entity, should trigger flush
        $task->execute($state); // batch has 2 entities, should be flushed
        $this->assertCount(0, $batchProperty->getValue($task)); // Batch should be empty after flush
    }


    public function testFlushCallsWriteBatch(): void
    {
        $state = $this->createStub(ProcessState::class);
        $task = $this->getMockBuilder(DoctrineBatchWriterTask::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['writeBatch'])
            ->getMock();

        $task->expects($this->once())->method('writeBatch')->with($state);
        $task->flush($state);
    }

    public function testWriteBatchWithEmptyBatchSkipsState(): void
    {
        $state = $this->createMock(ProcessState::class);
        $state->expects($this->once())->method('setSkipped')->with(true);

        $managerRegistry = $this->createStub(ManagerRegistry::class);
        $task = new DoctrineBatchWriterTask($managerRegistry);

        // Ensure batch is empty
        $reflection = new \ReflectionClass(DoctrineBatchWriterTask::class);
        $batchProperty = $reflection->getProperty('batch');
        $batchProperty->setValue($task, []);

        // Call writeBatch directly
        $writeBatchMethod = $reflection->getMethod('writeBatch');
        $writeBatchMethod->invoke($task, $state);
    }

    public function testWriteBatchPersistsAndFlushesEntities(): void
    {
        $entity1 = new \stdClass();
        $entity2 = new \stdClass();
        $state = $this->createMock(ProcessState::class); // Use mock to set expectation on setSkipped
        $state->expects($this->never())->method('setSkipped'); // Should not be skipped if batch is not empty

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->exactly(2))->method('persist'); // Called for entity1 and entity2
        $entityManager->expects($this->once())->method('flush');

        $managerRegistry = $this->createStub(ManagerRegistry::class);
        $managerRegistry->method('getManagerForClass')->willReturn($entityManager);

        $task = new DoctrineBatchWriterTask($managerRegistry);

        // Manually set batch
        $reflection = new \ReflectionClass(DoctrineBatchWriterTask::class);
        $batchProperty = $reflection->getProperty('batch');
        $batchProperty->setValue($task, [$entity1, $entity2]);

        // Call writeBatch directly
        $writeBatchMethod = $reflection->getMethod('writeBatch');
        $writeBatchMethod->setAccessible(true);
        $writeBatchMethod->invoke($task, $state);
    }

    public function testWriteBatchClearsBatchAfterFlushing(): void
    {
        $entity = new \stdClass();
        $state = $this->createStub(ProcessState::class);

        $entityManager = $this->createStub(EntityManagerInterface::class);
        $managerRegistry = $this->createStub(ManagerRegistry::class);
        $managerRegistry->method('getManagerForClass')->willReturn($entityManager);

        $task = new DoctrineBatchWriterTask($managerRegistry);

        $reflection = new \ReflectionClass(DoctrineBatchWriterTask::class);
        $batchProperty = $reflection->getProperty('batch');
        $batchProperty->setAccessible(true);
        $batchProperty->setValue($task, [$entity]); // Add an entity to the batch

        // Call writeBatch directly
        $writeBatchMethod = $reflection->getMethod('writeBatch');
        $writeBatchMethod->setAccessible(true);
        $writeBatchMethod->invoke($task, $state);

        $this->assertCount(0, $batchProperty->getValue($task)); // Batch should be empty
    }

    public function testWriteBatchSetsOutput(): void
    {
        $entity = new \stdClass();
        $state = $this->createMock(ProcessState::class); // Use mock to set expectation on setOutput
        $state->expects($this->once())->method('setOutput')->with([$entity]);

        $entityManager = $this->createStub(EntityManagerInterface::class);
        $managerRegistry = $this->createStub(ManagerRegistry::class);
        $managerRegistry->method('getManagerForClass')->willReturn($entityManager);

        $task = new DoctrineBatchWriterTask($managerRegistry);

        $reflection = new \ReflectionClass(DoctrineBatchWriterTask::class);
        $batchProperty = $reflection->getProperty('batch');
        $batchProperty->setAccessible(true);
        $batchProperty->setValue($task, [$entity]); // Add an entity to the batch

        // Call writeBatch directly
        $writeBatchMethod = $reflection->getMethod('writeBatch');
        $writeBatchMethod->setAccessible(true);
        $writeBatchMethod->invoke($task, $state);
    }

    public function testWriteBatchThrowsExceptionWhenNoManagerFound(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        $entity = new \stdClass();
        $state = $this->createStub(ProcessState::class);

        $managerRegistry = $this->createStub(ManagerRegistry::class);
        $managerRegistry->method('getManagerForClass')->willReturn(null); // Simulate no manager found

        $task = new DoctrineBatchWriterTask($managerRegistry);

        $reflection = new \ReflectionClass(DoctrineBatchWriterTask::class);
        $batchProperty = $reflection->getProperty('batch');
        $batchProperty->setAccessible(true);
        $batchProperty->setValue($task, [$entity]); // Add an entity to the batch

        // Call writeBatch directly
        $writeBatchMethod = $reflection->getMethod('writeBatch');
        $writeBatchMethod->setAccessible(true);
        $writeBatchMethod->invoke($task, $state);
    }
}
