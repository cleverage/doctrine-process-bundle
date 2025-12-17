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

use CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineRefresherTask;
use CleverAge\ProcessBundle\Model\ProcessState;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DoctrineRefresherTask::class)]
class DoctrineRefresherTaskTest extends TestCase
{
    /**
     * @param array<string, mixed> $options
     */
    protected function getTask(array $options = [], ?ManagerRegistry $managerRegistry = null): DoctrineRefresherTask
    {
        if (!$managerRegistry instanceof ManagerRegistry) {
            $managerRegistry = $this->createStub(ManagerRegistry::class);
        }
        $task = new DoctrineRefresherTask($managerRegistry);

        $state = $this->createStub(ProcessState::class);
        $state->method('getContextualizedOptions')->willReturn($options);
        $task->initialize($state);

        return $task;
    }

    public function testExecuteRefreshesEntity(): void
    {
        $entity = new \stdClass();
        $state = $this->createMock(ProcessState::class);
        $state->method('getInput')->willReturn($entity);
        $state->expects($this->once())->method('setOutput')->with($entity);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('refresh')->with($entity);

        $managerRegistry = $this->createStub(ManagerRegistry::class);
        $managerRegistry->method('getManagerForClass')->willReturn($entityManager);

        $task = $this->getTask([], $managerRegistry);
        $task->execute($state);
    }

    public function testExecuteThrowsExceptionOnNullInput(): void
    {
        $this->expectException(\RuntimeException::class);

        $state = $this->createStub(ProcessState::class);
        $state->method('getInput')->willReturn(null);

        $task = $this->getTask();
        $task->execute($state);
    }

    public function testExecuteThrowsExceptionWhenNoManagerFound(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        $entity = new \stdClass();
        $state = $this->createStub(ProcessState::class);
        $state->method('getInput')->willReturn($entity);

        $managerRegistry = $this->createStub(ManagerRegistry::class);
        $managerRegistry->method('getManagerForClass')->willReturn(null);

        $task = $this->getTask([], $managerRegistry);
        $task->execute($state);
    }
}
