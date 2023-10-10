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

use CleverAge\ProcessBundle\Model\ProcessState;

/**
 * Clear Doctrine's unit of work.
 */
class ClearEntityManagerTask extends AbstractDoctrineTask
{
    public function execute(ProcessState $state): void
    {
        $entityManager = $this->getManager($state);
        $entityManager->clear();
    }
}
