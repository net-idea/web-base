<?php

declare(strict_types=1);

namespace NetIdea\WebBase\Repository;

use NetIdea\WebBase\Entity\FormContactEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FormContactEntity>
 */
class FormContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormContactEntity::class);
    }
}
