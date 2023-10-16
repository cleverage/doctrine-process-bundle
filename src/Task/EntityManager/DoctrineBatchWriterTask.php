<?php

declare(strict_types=1);

/*
 * This file is part of the CleverAge/DoctrineProcessBundle package.
 *
 * Copyright (c) 2017-2023 Clever-Age
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CleverAge\DoctrineProcessBundle\Task\EntityManager;

use CleverAge\ProcessBundle\Model\FlushableTaskInterface;
use CleverAge\ProcessBundle\Model\ProcessState;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Persists Doctrine entities.
 */
class DoctrineBatchWriterTask extends AbstractDoctrineTask implements FlushableTaskInterface
{
    protected array $batch = [];

    public function flush(ProcessState $state): void
    {
        $this->writeBatch($state);
    }

    public function execute(ProcessState $state): void
    {
        $this->batch[] = $state->getInput();

        if (\count($this->batch) >= $this->getOption($state, 'batch_count')) {
            $this->writeBatch($state);
        } else {
            $state->setSkipped(true);
        }
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'batch_count' => 10,
        ]);
        $resolver->setAllowedTypes('batch_count', ['integer']);
    }

    protected function writeBatch(ProcessState $state): void
    {
        if (0 === \count($this->batch)) {
            $state->setSkipped(true);

            return;
        }

        // Support for multiple entity managers is overkill but might be necessary
        $entityManagers = new \SplObjectStorage();
        foreach ($this->batch as $entity) {
            $class = ClassUtils::getClass($entity);
            $entityManager = $this->doctrine->getManagerForClass($class);
            if (!$entityManager instanceof EntityManagerInterface) {
                throw new \UnexpectedValueException("No manager found for class {$class}");
            }
            $entityManager->persist($entity);
            $entityManagers->attach($entityManager);
        }

        foreach ($entityManagers as $entityManager) {
            $entityManager->flush();
        }

        $state->setOutput($this->batch);
        $this->batch = [];
    }
}
