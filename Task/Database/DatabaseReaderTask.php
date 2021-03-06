<?php declare(strict_types=1);
/**
 * This file is part of the CleverAge/DoctrineProcessBundle package.
 *
 * Copyright (C) 2017-2019 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\DoctrineProcessBundle\Task\Database;

use CleverAge\ProcessBundle\Model\AbstractConfigurableTask;
use CleverAge\ProcessBundle\Model\FinalizableTaskInterface;
use CleverAge\ProcessBundle\Model\IterableTaskInterface;
use CleverAge\ProcessBundle\Model\ProcessState;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\PDOStatement;
use Doctrine\DBAL\Driver\ResultStatement;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Fetch entities from doctrine
 *
 * @author Valentin Clavreul <vclavreul@clever-age.com>
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class DatabaseReaderTask extends AbstractConfigurableTask implements IterableTaskInterface, FinalizableTaskInterface
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var ManagerRegistry */
    protected $doctrine;

    /** @var PDOStatement */
    protected $statement;

    /** @var array|mixed */
    protected $nextItem;

    /**
     * @param LoggerInterface $logger
     * @param ManagerRegistry $doctrine
     */
    public function __construct(LoggerInterface $logger, ManagerRegistry $doctrine)
    {
        $this->logger = $logger;
        $this->doctrine = $doctrine;
    }

    /**
     * Moves the internal pointer to the next element,
     * return true if the task has a next element
     * return false if the task has terminated it's iteration
     *
     * @param ProcessState $state
     *
     * @throws \LogicException
     *
     * @return bool
     */
    public function next(ProcessState $state): bool
    {
        if (!$this->statement) {
            return false;
        }

        $this->nextItem = $this->statement->fetch();

        return (bool) $this->nextItem;
    }

    /**
     * @param ProcessState $state
     *
     * @throws \InvalidArgumentException
     * @throws ExceptionInterface
     * @throws DBALException
     */
    public function execute(ProcessState $state): void
    {
        $options = $this->getOptions($state);
        if (!$this->statement) {
            $this->statement = $this->initializeStatement($state);
        }

        // Check if the next item has been stored by the next() call
        if (null !== $this->nextItem) {
            $result = $this->nextItem;
            $this->nextItem = null;
        } else {
            $result = $this->statement->fetch();
        }

        // Handle empty results
        if (false === $result) {
            $logContext = ['options' => $options];
            $this->logger->log($options['empty_log_level'], 'Empty resultset for query', $logContext);
            $state->setSkipped(true);
            $this->statement = null;

            return;
        }

        if (null !== $options['paginate']) {
            $results = [];
            $i = 0;
            while (false !== $result && $i++ < $options['paginate']) {
                $results[] = $result;
                $result = $this->statement->fetch();
            }
            $state->setOutput($results);
        } else {
            $state->setOutput($result);
        }
    }

    /**
     * @param ProcessState $state
     */
    public function finalize(ProcessState $state)
    {
        if ($this->statement) {
            $this->statement->closeCursor();
        }
    }

    /**
     * @param ProcessState $state
     *
     * @throws ExceptionInterface
     * @throws \InvalidArgumentException
     * @throws DBALException
     *
     * @return ResultStatement
     */
    protected function initializeStatement(ProcessState $state): ResultStatement
    {
        $options = $this->getOptions($state);
        $connection = $this->getConnection($state);
        $sql = $options['sql'];

        if (null === $sql) {
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

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(
            [
                'table',
            ]
        );
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

    /**
     * @param ProcessState $state
     *
     * @throws \InvalidArgumentException
     * @throws ExceptionInterface
     *
     * @return Connection
     */
    protected function getConnection(ProcessState $state): Connection
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */

        return $this->doctrine->getConnection($this->getOption($state, 'connection'));
    }
}
