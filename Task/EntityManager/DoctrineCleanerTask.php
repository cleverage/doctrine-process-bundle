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
 * Clean Doctrine entities from unit of work
 *
 * @author Madeline Veyrenc <mveyrenc@clever-age.com>
 */
class DoctrineCleanerTask extends AbstractDoctrineTask
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
        if (null === $entity) {
            throw new \RuntimeException('DoctrineWriterTask does not allow null input');
        }
        $class = ClassUtils::getClass($entity);
        $entityManager = $this->doctrine->getManagerForClass($class);
        if (!$entityManager instanceof EntityManagerInterface) {
            throw new \UnexpectedValueException("No manager found for class {$class}");
        }
        $entityManager->clear();
    }
}
