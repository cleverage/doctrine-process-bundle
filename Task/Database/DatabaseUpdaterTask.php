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
use CleverAge\ProcessBundle\Model\ProcessState;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\ResultStatement;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Execute an update/delete in the database from a SQL statement
 *
 * @see https://www.doctrine-project.org/projects/doctrine-dbal/en/2.9/reference/data-retrieval-and-manipulation.html#list-of-parameters-conversion
 *
 * @author Valentin Clavreul <vclavreul@clever-age.com>
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class DatabaseUpdaterTask extends AbstractConfigurableTask
{
    /** @var ManagerRegistry */
    protected $doctrine;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * @param ManagerRegistry $doctrine
     * @param LoggerInterface $logger
     */
    public function __construct(ManagerRegistry $doctrine, LoggerInterface $logger)
    {
        $this->doctrine = $doctrine;
        $this->logger = $logger;
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
        $numberRows = $this->initializeStatement($state);

        $state->setOutput($numberRows);
    }

    /**
     * @param ProcessState $state
     *
     * @throws ExceptionInterface
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws DBALException
     *
     * @return Integer The number of affected rows.
     */
    protected function initializeStatement(ProcessState $state): Integer
    {
        $options = $this->getOptions($state);
        $connection = $this->getConnection($state);

        if ($options['input_as_params']) {
            $params = $state->getInput();
        } else {
            $params = $options['params'];
        }
        if (!is_array($params)) {
            throw new \UnexpectedValueException('Expecting an array of params');
        }

        return $connection->executeUpdate($options['sql'], $params, $options['types']);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(
            [
                'sql',
            ]
        );
        $resolver->setAllowedTypes('sql', ['string']);
        $resolver->setDefaults(
            [
                'connection' => null,
                'input_as_params' => true,
                'params' => [],
                'types' => [],
            ]
        );
        $resolver->setAllowedTypes('connection', ['null', 'string']);
        $resolver->setAllowedTypes('input_as_params', ['bool']);
        $resolver->setAllowedTypes('params', ['array']);
        $resolver->setAllowedTypes('types', ['array']);
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
