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

use CleverAge\ProcessBundle\Model\FlushableTaskInterface;
use CleverAge\ProcessBundle\Model\ProcessState;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Persists Doctrine entities.
 *
 * @final since 3.3
 */
class DoctrineBatchWriterTask extends AbstractDoctrineTask implements FlushableTaskInterface
{
    /** @var array<object> */
    protected array $batch = [];

    public function flush(ProcessState $state): void
    {
        $this->writeBatch($state);
    }

    public function execute(ProcessState $state): void
    {
        /** @var object $input */
        $input = $state->getInput();
        $this->batch[] = $input;

        if (\count($this->batch) >= $this->getOption($state, 'batch_count')) {
            $this->writeBatch($state);
        } else {
            $state->setSkipped(true);
        }
    }

    #[\Override]
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
        if ([] === $this->batch) {
            $state->setSkipped(true);

            return;
        }

        // The buffer is emptied before writing: a batch whose writing fails must not be written again with the next ones
        $batch = $this->batch;
        $this->batch = [];

        // Support for multiple entity managers is overkill but might be necessary
        /** @var \SplObjectStorage<EntityManagerInterface, null> $entityManagers */
        $entityManagers = new \SplObjectStorage();
        try {
            foreach ($batch as $entity) {
                $class = ClassUtils::getClass($entity);
                $entityManager = $this->getEntityManager($state, $class);
                $entityManagers->offsetSet($entityManager);
                $entityManager->persist($entity);
            }

            foreach ($entityManagers as $entityManager) {
                $entityManager->flush();
            }
        } catch (\Throwable $exception) {
            $this->detachBatch($batch, $entityManagers);

            throw $exception;
        }

        $state->setOutput($batch);
    }

    /**
     * Detaches the entities of a batch whose writing failed from the entity managers still open (Doctrine closes an
     * entity manager when its flush fails), so that the next flush does not write them again.
     *
     * @param array<object>                                   $batch
     * @param \SplObjectStorage<EntityManagerInterface, null> $entityManagers
     */
    private function detachBatch(array $batch, \SplObjectStorage $entityManagers): void
    {
        foreach ($entityManagers as $entityManager) {
            if (!$entityManager->isOpen()) {
                continue;
            }
            foreach ($batch as $entity) {
                if ($entityManager->contains($entity)) {
                    $entityManager->detach($entity);
                }
            }
        }
    }
}
