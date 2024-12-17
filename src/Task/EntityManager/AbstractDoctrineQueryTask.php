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

namespace CleverAge\DoctrineProcessBundle\Task\EntityManager;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Psr\Log\LogLevel;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Easily extendable task to query entities in their repository.
 *
 * @phpstan-type Options array{
 *       'class_name': class-string,
 *       'criteria': array<string, string|array<string|int>|null>,
 *       'order_by': array<string, string|null>,
 *       'limit': ?int,
 *       'offset': ?int,
 *       'empty_log_level': string,
 * }
 */
abstract class AbstractDoctrineQueryTask extends AbstractDoctrineTask
{
    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setRequired(['class_name']);
        $resolver->setAllowedTypes('class_name', ['string']);
        $resolver->setDefaults(
            [
                'criteria' => [],
                'order_by' => [],
                'limit' => null,
                'offset' => null,
                'empty_log_level' => LogLevel::WARNING,
            ]
        );
        $resolver->setAllowedTypes('criteria', ['array']);
        $resolver->setAllowedTypes('order_by', ['array']);
        $resolver->setAllowedTypes('limit', ['null', 'integer']);
        $resolver->setAllowedTypes('offset', ['null', 'integer']);
        $resolver->setAllowedValues(
            'empty_log_level',
            [
                LogLevel::ALERT,
                LogLevel::CRITICAL,
                LogLevel::DEBUG,
                LogLevel::EMERGENCY,
                LogLevel::ERROR,
                LogLevel::INFO,
                LogLevel::NOTICE,
                LogLevel::WARNING,
            ]
        );
    }

    /**
     * @template TEntityClass of object
     *
     * @param EntityRepository<TEntityClass>               $repository
     * @param array<string, string|array<string|int>|null> $criteria
     * @param array<string, string|null>                   $orderBy
     */
    protected function getQueryBuilder(
        EntityRepository $repository,
        array $criteria,
        array $orderBy,
        ?int $limit = null,
        ?int $offset = null,
    ): QueryBuilder {
        $qb = $repository->createQueryBuilder('e');
        foreach ($criteria as $field => $value) {
            if (preg_match('/[^a-zA-Z0-9]/', $field)) {
                throw new \UnexpectedValueException("Forbidden field name '{$field}'");
            }
            $parameterName = 'param_'.bin2hex(random_bytes(4));
            if (null === $value) {
                $qb->andWhere("e.{$field} IS null");
            } else {
                if (\is_array($value)) {
                    $qb->andWhere("e.{$field} IN (:{$parameterName})");
                } else {
                    $qb->andWhere("e.{$field} = :{$parameterName}");
                }
                $qb->setParameter($parameterName, $value);
            }
        }
        foreach ($orderBy as $field => $order) {
            $qb->addOrderBy("e.{$field}", $order);
        }
        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }
        if (null !== $offset) {
            $qb->setFirstResult($offset);
        }

        return $qb;
    }
}
