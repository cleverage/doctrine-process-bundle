<?php declare(strict_types=1);
/**
 * This file is part of the CleverAge/ProcessBundle package.
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
 * Fetch entities from doctrine
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
        $statement = $this->initializeStatement($state);

        if (false === $statement->execute()) {
            throw new \RuntimeException("Error while executing query: {$statement->errorInfo()}");
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
        $connection = $this->getConnection($state);

        $input = $state->getInput();
        $params = is_array($input) ? $input : [];

        return $connection->executeQuery($this->getOption($state, 'sql'), $params);
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
            ]
        );
        $resolver->setAllowedTypes('connection', ['NULL', 'string']);
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
