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

use CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineDetacherTask;
use CleverAge\ProcessBundle\Model\ProcessState;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DoctrineDetacherTask::class)]
class DoctrineDetacherTaskTest extends TestCase
{
    /**
     * @param array<string, mixed> $options
     */
    protected function getTask(array $options = [], ?ManagerRegistry $managerRegistry = null): DoctrineDetacherTask
    {
        if (!$managerRegistry instanceof ManagerRegistry) {
            $managerRegistry = $this->createStub(ManagerRegistry::class);
        }
        $task = new DoctrineDetacherTask($managerRegistry);

        $state = $this->createStub(ProcessState::class);
        $state->method('getContextualizedOptions')->willReturn($options);
        $task->initialize($state);

        return $task;
    }

    public function testExecuteDetachesEntity(): void
    {
        $entity = new \stdClass();
        $state = $this->createStub(ProcessState::class);
        $state->method('getInput')->willReturn($entity);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('detach')->with($entity);

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
