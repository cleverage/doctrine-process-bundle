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

namespace CleverAge\DoctrineProcessBundle\Task\EntityManager;

use CleverAge\ProcessBundle\Model\IterableTaskInterface;
use CleverAge\ProcessBundle\Model\ProcessState;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use UnexpectedValueException;

/**
 * Fetch entities from doctrine
 */
class DoctrineReaderTask extends AbstractDoctrineQueryTask implements IterableTaskInterface
{
    protected ?IterableResult $iterator = null;

    public function __construct(
        protected LoggerInterface $logger,
        ManagerRegistry $doctrine
    ) {
        parent::__construct($doctrine);
    }

    /**
     * Moves the internal pointer to the next element,
     * return true if the task has a next element
     * return false if the task has terminated it's iteration
     */
    public function next(ProcessState $state): bool
    {
        if (! $this->iterator) {
            return false;
        }
        $this->iterator->next();

        return $this->iterator->valid();
    }

    public function execute(ProcessState $state): void
    {
        $options = $this->getOptions($state);
        if (! $this->iterator) {
            $class = $options['class_name'];
            $entityManager = $this->doctrine->getManagerForClass($class);
            if (! $entityManager instanceof EntityManagerInterface) {
                throw new UnexpectedValueException("No manager found for class {$class}");
            }
            $repository = $entityManager->getRepository($class);
            if (! $repository instanceof EntityRepository) {
                throw new UnexpectedValueException("No repository found for class {$class}");
            }
            $this->initIterator($repository, $options);
        }

        $result = $this->iterator->current();

        // Handle empty results
        if ($result === false) {
            $logContext = [
                'options' => $options,
            ];
            $this->logger->log($options['empty_log_level'], 'Empty resultset for query', $logContext);
            $state->setSkipped(true);
            $this->iterator = null;

            return;
        }

        $state->setOutput(reset($result));
    }

    protected function initIterator(EntityRepository $repository, array $options): void
    {
        $qb = $this->getQueryBuilder(
            $repository,
            $options['criteria'],
            $options['order_by'],
            $options['limit'],
            $options['offset']
        );

        $this->iterator = $qb->getQuery()
            ->iterate();
        $this->iterator->next(); // Move to first element
    }
}
