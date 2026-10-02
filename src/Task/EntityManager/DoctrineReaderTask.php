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

use CleverAge\ProcessBundle\Model\IterableTaskInterface;
use CleverAge\ProcessBundle\Model\ProcessState;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

/**
 * Fetch entities from doctrine.
 *
 * @phpstan-import-type Options from AbstractDoctrineQueryTask
 */
class DoctrineReaderTask extends AbstractDoctrineQueryTask implements IterableTaskInterface
{
    protected ?\Iterator $iterator = null;

    public function __construct(
        protected LoggerInterface $logger,
        ManagerRegistry $doctrine,
    ) {
        parent::__construct($doctrine);
    }

    /**
     * Moves the internal pointer to the next element,
     * return true if the task has a next element
     * return false if the task has terminated it's iteration.
     */
    public function next(ProcessState $state): bool
    {
        if (!$this->iterator instanceof \Iterator) {
            return false;
        }
        $this->iterator->next();
        if ($this->iterator->valid()) {
            return true;
        }

        // End of the iteration: the next input executes the query again
        $this->iterator = null;

        return false;
    }

    public function execute(ProcessState $state): void
    {
        /** @var Options $options */
        $options = $this->getOptions($state);
        if (!$this->iterator instanceof \Iterator) {
            /** @var class-string $class */
            $class = $options['class_name'];
            $entityManager = $this->getEntityManager($state, $class);
            $repository = $entityManager->getRepository($class);
            $this->initIterator($repository, $options);
        }
        if ($this->iterator instanceof \Iterator) {
            $result = $this->iterator->current();
        }

        // Handle empty results
        if (!isset($result) || false === $result) {
            $logContext = [
                'options' => $options,
            ];
            $this->logger->log($options['empty_log_level'], 'Empty resultset for query', $logContext);
            $state->setSkipped(true);
            $this->iterator = null;

            return;
        }

        $state->setOutput($result);
    }

    /**
     * @template TEntityClass of object
     *
     * @param EntityRepository<TEntityClass> $repository
     * @param Options                        $options
     */
    protected function initIterator(EntityRepository $repository, array $options): void
    {
        $qb = $this->getQueryBuilder(
            $repository,
            $options['criteria'],
            $options['order_by'],
            $options['limit'],
            $options['offset']
        );

        // Keep the iterator, so the entities are hydrated one at a time while the process iterates
        $iterable = $qb->getQuery()->toIterable();
        $this->iterator = \is_array($iterable) ? new \ArrayIterator($iterable) : new \IteratorIterator($iterable);
        $this->iterator->rewind();
    }
}
