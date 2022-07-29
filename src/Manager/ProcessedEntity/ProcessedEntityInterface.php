<?php

namespace Insitaction\RequestManagerBundle\Manager\ProcessedEntity;

use Doctrine\ORM\EntityManagerInterface;
use Insitaction\RequestManagerBundle\Manager\Entity\RequestEntityInterface;

interface ProcessedEntityInterface
{
    public function __construct(EntityManagerInterface $em);

    /**
     * @return RequestEntityInterface|RequestEntityInterface[]
     */
    public function save(): RequestEntityInterface|array;

    /**
     * @param RequestEntityInterface|RequestEntityInterface[] $entities
     */
    public function setAdaptedEntity(RequestEntityInterface|array $entities): void;

    /** @return RequestEntityInterface|RequestEntityInterface[] */
    public function getEntity(): RequestEntityInterface|array;
}
