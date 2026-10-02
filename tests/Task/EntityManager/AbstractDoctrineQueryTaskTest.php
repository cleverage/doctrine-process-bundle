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

    public function testGetQueryBuilder(): void
    {
        $qb = $this->getQueryBuilder(
            ['title' => 'It', 'deletedAt' => null, 'id' => [1, 3]],
            ['title' => 'ASC', 'id' => null],
            10,
            20
        );

        self::assertMatchesRegularExpression(
            '/^SELECT e FROM App\\\\Entity\\\\Book e WHERE e\\.title = :(param_[0-9a-f]{8}) AND e\\.deletedAt IS null AND e\\.id IN \\(:(param_[0-9a-f]{8})\\) ORDER BY e\\.title ASC, e\\.id ASC$/',
            $qb->getDQL()
        );
        $parameters = [];
        foreach ($qb->getParameters() as $parameter) {
            $parameters[] = $parameter->getValue();
        }
        // No parameter for the null criteria, ASC by default for a null order
        self::assertSame(['It', [1, 3]], $parameters);
        self::assertSame(10, $qb->getMaxResults());
        self::assertSame(20, $qb->getFirstResult());
    }

    public function testGetQueryBuilderWithoutCriteria(): void
    {
        $qb = $this->getQueryBuilder([], []);

        self::assertSame('SELECT e FROM App\\Entity\\Book e', $qb->getDQL());
        self::assertNull($qb->getMaxResults());
        self::assertSame(0, $qb->getFirstResult());
    }

    /**
     * @param array<string, string|array<string|int>|null> $criteria
     * @param array<string, string|null>                   $orderBy
     */
    private function getQueryBuilder(array $criteria, array $orderBy, ?int $limit = null, ?int $offset = null): QueryBuilder
    {
        $task = $this->createStub(AbstractDoctrineQueryTask::class);
        $repository = $this->createStub(EntityRepository::class);
        $repository->method('createQueryBuilder')->willReturn(
            (new QueryBuilder($this->createStub(EntityManagerInterface::class)))->select('e')->from('App\\Entity\\Book', 'e')
        );

        /** @var QueryBuilder $qb */
        $qb = (new \ReflectionMethod(AbstractDoctrineQueryTask::class, 'getQueryBuilder'))
            ->invoke($task, $repository, $criteria, $orderBy, $limit, $offset);

        return $qb;
    }
}
