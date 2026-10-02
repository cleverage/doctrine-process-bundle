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

use CleverAge\DoctrineProcessBundle\Task\EntityManager\AbstractDoctrineTask;
use CleverAge\DoctrineProcessBundle\Task\EntityManager\DoctrineWriterTask;
use CleverAge\ProcessBundle\Configuration\ProcessConfiguration;
use CleverAge\ProcessBundle\Configuration\TaskConfiguration;
use CleverAge\ProcessBundle\Context\ContextualOptionResolver;
use CleverAge\ProcessBundle\Model\ProcessHistory;
use CleverAge\ProcessBundle\Model\ProcessState;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Entity manager selection (entity_manager option), through DoctrineWriterTask.
 */
#[CoversClass(AbstractDoctrineTask::class)]
#[UsesClass(DoctrineWriterTask::class)]
class AbstractDoctrineTaskTest extends TestCase
{
    public function testEntityManagerOfTheClassByDefault(): void
    {
        $entity = new \stdClass();
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist')->with($entity);

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects(self::once())->method('getManagerForClass')->with(\stdClass::class)->willReturn($entityManager);
        $doctrine->expects(self::never())->method('getManager');

        $this->execute($doctrine, [], $entity);
    }

    public function testEntityManagerOption(): void
    {
        $entity = new \stdClass();
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist')->with($entity);

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine->expects(self::once())->method('getManager')->with('customer')->willReturn($entityManager);
        $doctrine->expects(self::never())->method('getManagerForClass');

        $this->execute($doctrine, ['entity_manager' => 'customer'], $entity);
    }

    public function testNotAnEntityManager(): void
    {
        $doctrine = $this->createStub(ManagerRegistry::class);
        $doctrine->method('getManager')->willReturn($this->createStub(ObjectManager::class));

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('No manager found for class stdClass');
        $this->execute($doctrine, ['entity_manager' => 'odm'], new \stdClass());
    }

    /**
     * @param array<string, mixed> $options
     */
    private function execute(ManagerRegistry $doctrine, array $options, object $entity): void
    {
        $processConfiguration = new ProcessConfiguration('test', []);
        $state = new ProcessState($processConfiguration, new ProcessHistory($processConfiguration));
        $state->setContextualOptionResolver(new ContextualOptionResolver());
        $state->setContext([]);
        $state->setTaskConfiguration(new TaskConfiguration('write', DoctrineWriterTask::class, $options));
        $state->setInput($entity);

        $task = new DoctrineWriterTask($doctrine);
        $task->initialize($state);
        $task->execute($state);
    }
}
