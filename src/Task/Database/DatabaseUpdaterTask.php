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
use CleverAge\ProcessBundle\Model\ProcessState;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Execute an update/delete in the database from a SQL statement.
 *
 * @see https://www.doctrine-project.org/projects/doctrine-dbal/en/2.9/reference/data-retrieval-and-manipulation.html#list-of-parameters-conversion
 */
class DatabaseUpdaterTask extends AbstractConfigurableTask
{
    public function __construct(
        protected ManagerRegistry $doctrine,
        protected LoggerInterface $logger
    ) {
    }

    public function execute(ProcessState $state): void
    {
        $numberRows = $this->initializeStatement($state);

        $state->setOutput($numberRows);
    }

    /**
     * @return int the number of affected rows
     *
     * @throws Exception
     */
    protected function initializeStatement(ProcessState $state): int
    {
        $options = $this->getOptions($state);
        $connection = $this->getConnection($state);

        if ($options['input_as_params']) {
            $params = $state->getInput();
        } else {
            $params = $options['params'];
        }
        if (!\is_array($params)) {
            throw new \UnexpectedValueException('Expecting an array of params');
        }

        return $connection->executeStatement($options['sql'], $params, $options['types']);
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['sql']);
        $resolver->setAllowedTypes('sql', ['string']);
        $resolver->setDefaults([
            'connection' => null,
            'input_as_params' => true,
            'params' => [],
            'types' => [],
        ]);
        $resolver->setAllowedTypes('connection', ['null', 'string']);
        $resolver->setAllowedTypes('input_as_params', ['bool']);
        $resolver->setAllowedTypes('params', ['array']);
        $resolver->setAllowedTypes('types', ['array']);
    }

    protected function getConnection(ProcessState $state): Connection
    {
        /* @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->doctrine->getConnection($this->getOption($state, 'connection'));
    }
}
