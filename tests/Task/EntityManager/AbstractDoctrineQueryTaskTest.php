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

use CleverAge\DoctrineProcessBundle\Task\EntityManager\AbstractDoctrineQueryTask;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractDoctrineQueryTask::class)]
class AbstractDoctrineQueryTaskTest extends TestCase
{
    public function testGetQueryBuilderWithInvalidField(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        $task = $this->createStub(AbstractDoctrineQueryTask::class);
        $repository = $this->createStub(EntityRepository::class);
        $repository->method('createQueryBuilder')->willReturn(new QueryBuilder($this->createStub(EntityManagerInterface::class)));

        $reflection = new \ReflectionClass(AbstractDoctrineQueryTask::class);
        $method = $reflection->getMethod('getQueryBuilder');

        $method->invoke($task, $repository, ['e.field; DROP TABLE dummy;' => 'value'], []);
    }
}
