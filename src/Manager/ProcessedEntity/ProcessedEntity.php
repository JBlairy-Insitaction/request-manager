<?php

namespace Insitaction\RequestManagerBundle\Manager\ProcessedEntity;

use Doctrine\ORM\EntityManagerInterface;
use Insitaction\RequestManagerBundle\Manager\Entity\RequestEntityInterface;

class ProcessedEntity implements ProcessedEntityInterface
{
    /** @var RequestEntityInterface|RequestEntityInterface[] */
    private $entities;
    private EntityManagerInterface $em;

    public function __construct(
        EntityManagerInterface $em
    ) {
        $this->em = $em;
    }

    /** @return RequestEntityInterface|RequestEntityInterface[] */
    public function getEntity()
    {
        return $this->entities;
    }

    /**
     * @param RequestEntityInterface|RequestEntityInterface[] $entities
     */
    public function setAdaptedEntity($entities): void
    {
        $this->entities = $entities;
    }

    /** @return RequestEntityInterface|RequestEntityInterface[] */
    public function save()
    {
        if (is_array($this->entities)) {
            foreach ($this->entities as $entity) {
                $this->processSave($entity);
            }
        } else {
            $this->processSave($this->entities);
        }

        return $this->entities;
    }

    private function processSave(RequestEntityInterface $entity): void
    {
        $this->em->persist($entity);
        $this->em->flush();
    }
}
