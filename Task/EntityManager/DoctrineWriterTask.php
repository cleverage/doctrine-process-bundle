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
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Persists and flush Doctrine entities
 *
 * @author Valentin Clavreul <vclavreul@clever-age.com>
 * @author Vincent Chalnot <vchalnot@clever-age.com>
 */
class DoctrineWriterTask extends AbstractDoctrineTask
{
    /**
     * @param ProcessState $state
     *
     * @throws ORMException
     * @throws ExceptionInterface
     */
    public function execute(ProcessState $state): void
    {
        $state->setOutput($this->writeEntity($state));
    }

    /**
     * @param OptionsResolver $resolver
     *
     * @throws \UnexpectedValueException
     * @throws UndefinedOptionsException
     * @throws AccessException
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(
            [
                'global_flush' => true,
            ]
        );
        $resolver->setAllowedTypes('global_flush', ['boolean']);
    }

    /**
     * @param ProcessState $state
     *
     * @throws ExceptionInterface
     * @throws ORMException
     *
     * @return mixed
     */
    protected function writeEntity(ProcessState $state)
    {
        $options = $this->getOptions($state);
        $entity = $state->getInput();

        if (null === $entity) {
            throw new \RuntimeException('DoctrineWriterTask does not allow null input');
        }
        $class = ClassUtils::getClass($entity);
        $entityManager = $this->doctrine->getManagerForClass($class);
        if (!$entityManager instanceof EntityManagerInterface) {
            throw new \UnexpectedValueException("No manager found for class {$class}");
        }
        $entityManager->persist($entity);

        if ($options['global_flush']) {
            $entityManager->flush();
        } else {
            if (!$entityManager instanceof EntityManager) {
                throw new \UnexpectedValueException("Manager for class {$class} does not support unitary flush");
            }
            $entityManager->flush($entity);
        }

        return $entity;
    }
}
