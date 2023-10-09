<?php

declare(strict_types=1);

/**
 * This file is part of the CleverAge/DoctrineProcessBundle package.
 *
 * Copyright (C) 2017-2023 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\DoctrineProcessBundle\Task\Database;

use CleverAge\ProcessBundle\Model\AbstractConfigurableTask;
use CleverAge\ProcessBundle\Model\FinalizableTaskInterface;
use CleverAge\ProcessBundle\Model\IterableTaskInterface;
use CleverAge\ProcessBundle\Model\ProcessState;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Fetch entities from doctrine
 */
class DatabaseReaderTask extends AbstractConfigurableTask implements IterableTaskInterface, FinalizableTaskInterface
{
    protected ?Result $statement = null;

    protected mixed $nextItem = null;

    public function __construct(
        protected LoggerInterface $logger,
        protected ManagerRegistry $doctrine
    ) {
    }

    /**
     * Moves the internal pointer to the next element,
     * return true if the task has a next element
     * return false if the task has terminated it's iteration
     */
    public function next(ProcessState $state): bool
    {
        if (! $this->statement) {
            return false;
        }

        $this->nextItem = $this->statement->fetchAssociative();

        return (bool) $this->nextItem;
    }

    public function execute(ProcessState $state): void
    {
        $options = $this->getOptions($state);
        if (! $this->statement) {
            $this->statement = $this->initializeStatement($state);
        }

        // Check if the next item has been stored by the next() call
        if ($this->nextItem !== null) {
            $result = $this->nextItem;
            $this->nextItem = null;
        } else {
            $result = $this->statement->fetchAssociative();
        }

        // Handle empty results
        if ($result === false) {
            $logContext = [
                'options' => $options,
            ];
            $this->logger->log($options['empty_log_level'], 'Empty resultset for query', $logContext);
            $state->setSkipped(true);
            $this->statement = null;

            return;
        }

        if ($options['paginate'] !== null) {
            $results = [];
            $i = 0;
            while ($result !== false && $i++ < $options['paginate']) {
                $results[] = $result;
                $result = $this->statement->fetchAssociative();
            }
            $state->setOutput($results);
        } else {
            $state->setOutput($result);
        }
    }

    public function finalize(ProcessState $state): void
    {
        $this->statement?->free();
    }

    protected function initializeStatement(ProcessState $state): Result
    {
        $options = $this->getOptions($state);
        $connection = $this->getConnection($state);
        $sql = $options['sql'];

        if ($sql === null) {
            $qb = $connection->createQueryBuilder();
            $qb
                ->select('tbl.*')
                ->from($options['table'], 'tbl');

            if ($options['limit']) {
                $qb->setMaxResults($options['limit']);
            }
            if ($options['offset']) {
                $qb->setFirstResult($options['offset']);
            }

            $sql = $qb->getSQL();
        }
        if ($options['input_as_params']) {
            $params = $state->getInput();
        } else {
            $params = $options['params'];
        }

        return $connection->executeQuery($sql, $params, $options['types']);
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['table']);
        $resolver->setAllowedTypes('table', ['string']);
        $resolver->setDefaults(
            [
                'connection' => null,
                'sql' => null,
                'limit' => null,
                'offset' => null,
                'paginate' => null,
                'input_as_params' => false,
                'params' => [],
                'types' => [],
                'empty_log_level' => LogLevel::WARNING,
            ]
        );
        $resolver->setAllowedTypes('connection', ['null', 'string']);
        $resolver->setAllowedTypes('sql', ['null', 'string']);
        $resolver->setAllowedTypes('paginate', ['null', 'int']);
        $resolver->setAllowedTypes('limit', ['null', 'integer']);
        $resolver->setAllowedTypes('offset', ['null', 'integer']);
        $resolver->setAllowedTypes('input_as_params', ['bool']);
        $resolver->setAllowedTypes('params', ['array']);
        $resolver->setAllowedTypes('types', ['array']);
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

    protected function getConnection(ProcessState $state): Connection
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->doctrine->getConnection($this->getOption($state, 'connection'));
    }
}
