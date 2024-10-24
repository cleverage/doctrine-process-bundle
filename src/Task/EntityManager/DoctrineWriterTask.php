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
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Persists and flush Doctrine entities.
 */
class DoctrineWriterTask extends AbstractDoctrineTask
{
    public function execute(ProcessState $state): void
    {
        $state->setOutput($this->writeEntity($state));
    }

    protected function writeEntity(ProcessState $state): mixed
    {
        $this->getOptions($state);
        /** @var object $entity */
        $entity = $state->getInput();

        if (null === $entity) {
            throw new \RuntimeException('DoctrineWriterTask does not allow null input');
        }
        $class = ClassUtils::getClass($entity);
        /** @var ?EntityManager $entityManager */
        $entityManager = $this->doctrine->getManagerForClass($class);
        if (!$entityManager instanceof EntityManagerInterface) {
            throw new \UnexpectedValueException("No manager found for class {$class}");
        }
        $entityManager->persist($entity);

        $entityManager->flush();

        return $entity;
    }
}
