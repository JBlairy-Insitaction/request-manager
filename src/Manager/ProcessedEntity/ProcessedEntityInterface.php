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
    public function save();

    /**
     * @param RequestEntityInterface|RequestEntityInterface[] $entities
     */
    public function setAdaptedEntity($entities): void;

    /** @return RequestEntityInterface|RequestEntityInterface[] */
    public function getEntity();
}
