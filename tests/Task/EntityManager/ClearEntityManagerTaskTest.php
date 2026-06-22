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

use CleverAge\DoctrineProcessBundle\Task\EntityManager\ClearEntityManagerTask;
use CleverAge\ProcessBundle\Model\ProcessState;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ClearEntityManagerTask::class)]
class ClearEntityManagerTaskTest extends TestCase
{
    public function testExecute(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('clear');

        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $task = $this->getMockBuilder(ClearEntityManagerTask::class)
            ->setConstructorArgs([$managerRegistry])
            ->onlyMethods(['getOption']) // Mock only the getOption method
            ->getMock();

        // Ensure getOption returns null for 'entity_manager', mimicking default behavior
        $task->expects($this->once())
            ->method('getOption')
            ->with(self::anything(), 'entity_manager')
            ->willReturn(null);

        $processState = $this->createStub(ProcessState::class);

        $task->execute($processState);
    }
}
