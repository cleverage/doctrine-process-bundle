<?php declare(strict_types=1);
/**
 * This file is part of the CleverAge/DoctrineProcessBundle package.
 *
 * Copyright (C) 2017-2019 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\DoctrineProcessBundle\Task\EntityManager;

use CleverAge\ProcessBundle\Model\ProcessState;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMInvalidArgumentException;

/**
 * Remove Doctrine entities
 *
 * @author Valentin Clavreul <vclavreul@clever-age.com>
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class DoctrineRemoverTask extends AbstractDoctrineTask
{
    /**
     * @param ProcessState $state
     *
     * @throws \UnexpectedValueException
     * @throws ORMInvalidArgumentException
     * @throws \InvalidArgumentException
     */
    public function execute(ProcessState $state): void
    {
        $entity = $state->getInput();
        $class = ClassUtils::getClass($entity);
        $entityManager = $this->doctrine->getManagerForClass($class);
        if (!$entityManager instanceof EntityManagerInterface) {
            throw new \UnexpectedValueException("No manager found for class {$class}");
        }
        $entityManager->remove($entity);
        $entityManager->flush();
    }
}
