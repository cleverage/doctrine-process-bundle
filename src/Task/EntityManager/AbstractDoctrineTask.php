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

use CleverAge\ProcessBundle\Model\AbstractConfigurableTask;
use CleverAge\ProcessBundle\Model\ProcessState;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Base logic for doctrine related tasks.
 */
abstract class AbstractDoctrineTask extends AbstractConfigurableTask
{
    public function __construct(
        protected ManagerRegistry $doctrine,
    ) {
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'entity_manager' => null,
        ]);
        $resolver->setAllowedTypes('entity_manager', ['null', 'string']);
    }

    protected function getManager(ProcessState $state): ObjectManager
    {
        /** @var ?string $entityManagerName */
        $entityManagerName = $this->getOption($state, 'entity_manager');

        return $this->doctrine->getManager($entityManagerName);
    }
}
