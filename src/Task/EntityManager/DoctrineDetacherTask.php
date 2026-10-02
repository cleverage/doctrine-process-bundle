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
 * Detach Doctrine entities from unit of work.
 */
class DoctrineDetacherTask extends AbstractDoctrineTask
{
    public function execute(ProcessState $state): void
    {
        $entity = $state->getInput();
        if (null === $entity) {
            throw new \RuntimeException('DoctrineDetacherTask does not allow null input');
        }
        /** @var object $entity */
        $class = ClassUtils::getClass($entity);
        $entityManager = $this->getEntityManager($state, $class);
        $entityManager->detach($entity);
    }
}
