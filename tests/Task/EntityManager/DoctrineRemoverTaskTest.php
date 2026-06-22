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

use CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineRemoverTask;
use CleverAge\ProcessBundle\Model\ProcessState;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DoctrineRemoverTask::class)]
class DoctrineRemoverTaskTest extends TestCase
{
    public function testExecuteWithEntity(): void
    {
        $entity = new \stdClass();
        $state = $this->createStub(ProcessState::class);
        $state->method('getInput')->willReturn($entity);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('remove')->with($entity);
        $entityManager->expects($this->once())->method('flush');

        $managerRegistry = $this->createStub(ManagerRegistry::class);
        $managerRegistry->method('getManagerForClass')->willReturn($entityManager);

        $task = new DoctrineRemoverTask($managerRegistry);
        $task->execute($state);
    }

    public function testExecuteWithNullInput(): void
    {
        $this->expectException(\TypeError::class); // ClassUtils::getClass expects an object, null will cause a TypeError

        $state = $this->createStub(ProcessState::class);
        $state->method('getInput')->willReturn(null);

        $managerRegistry = $this->createStub(ManagerRegistry::class);

        $task = new DoctrineRemoverTask($managerRegistry);
        $task->execute($state);
    }

    public function testExecuteWithNoManager(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        $entity = new \stdClass();
        $state = $this->createStub(ProcessState::class);
        $state->method('getInput')->willReturn($entity);

        $managerRegistry = $this->createStub(ManagerRegistry::class);
        $managerRegistry->method('getManagerForClass')->willReturn(null);

        $task = new DoctrineRemoverTask($managerRegistry);
        $task->execute($state);
    }
}
