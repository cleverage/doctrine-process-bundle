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

use CleverAge\ProcessBundle\Model\ProcessState;
use Doctrine\Common\Util\ClassUtils;

/**
 * Refreshes a Doctrine entity from the database.
 */
class DoctrineRefresherTask extends AbstractDoctrineTask
{
    public function execute(ProcessState $state): void
    {
        $entity = $state->getInput();
        if (null === $entity) {
            throw new \RuntimeException('DoctrineRefresherTask does not allow null input');
        }
        /** @var object $entity */
        $class = ClassUtils::getClass($entity);
        $entityManager = $this->getEntityManager($state, $class);
        $entityManager->refresh($entity);

        $state->setOutput($entity);
    }
}
