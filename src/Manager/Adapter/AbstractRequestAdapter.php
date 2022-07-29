<?php

namespace Insitaction\RequestManagerBundle\Manager\Adapter;

use Doctrine\ORM\EntityManagerInterface;
use Insitaction\RequestManagerBundle\Manager\Entity\RequestEntityInterface;
use Insitaction\RequestManagerBundle\Manager\ProcessedEntity\ProcessedEntityInterface;
use RuntimeException;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

abstract class AbstractRequestAdapter implements RequestAdapterInterface
{
    /** @var class-string<RequestEntityInterface> */
    private string $entityClassName;

    /** @var array<string, mixed>|null */
    private ?array $extraFields = [];

    /** @var string[] */
    private array $groups;

    public function __construct(
        private SerializerInterface $serializer,
        private EntityManagerInterface $em,
        private ProcessedEntityInterface $processedEntity
    ) {
        $this->entityClassName = $this->getEntityClassname();
        $this->groups = $this->setGroups();
    }

    /**
     * @param array<string, mixed> $extraFields
     */
    public function addExtraFields(array $extraFields): self
    {
        $this->extraFields = $extraFields;

        return $this;
    }

    public function process(Request $request): ProcessedEntityInterface
    {
        $data = $this->getDataByMethod($request);

        if (true === $this->multiple()) {
            $entities = [];

            foreach ($data as $entity) {
                $this->validation($entity);
                $entities[] = $this->serialize(json_encode($entity, JSON_THROW_ON_ERROR), $this->getObject($entity));
            }
        } else {
            $this->validation($data);
            $entities = $this->serialize(json_encode($data, JSON_THROW_ON_ERROR), $this->getObject($data));
        }

        $this->processedEntity->setAdaptedEntity($entities);

        return $this->processedEntity;
    }

    /**
     * @return array<mixed, mixed>
     */
    private function getDataByMethod(Request $request): array
    {
        $requestContent = json_decode($request->getContent(), true);

        switch ($request->getMethod()) {
            case Request::METHOD_POST:
                if (0 !== $request->request->count()) {
                    return array_merge($request->request->all(), $this->extraFields);
                }
                break;
            case Request::METHOD_GET:
                if (0 !== $request->request->count()) {
                    return array_merge($request->query->all(), $this->extraFields);
                }
                break;
        }

        return array_merge(is_array($requestContent) ? $requestContent : [], $this->extraFields);
    }

    private function serialize(string $data, ?RequestEntityInterface $entity): RequestEntityInterface
    {
        $context = [
            'groups' => $this->groups,
        ];

        if (null !== $entity) {
            $context[AbstractNormalizer::OBJECT_TO_POPULATE] = $entity;
        }

        return $this->serializer->deserialize($data, $this->entityClassName, 'json', $context);
    }

    /**
     * @param array<mixed, mixed>|stdClass $data
     */
    private function getObject(array|stdClass $data): ?RequestEntityInterface
    {
        $data = (array)$data;

        if (!isset($data['id'])) {
            return null;
        }

        $entity = $this->em->getRepository($this->entityClassName)->find($data['id']);

        if (!$entity instanceof RequestEntityInterface) {
            return null;
        }

        return $entity;
    }

    /**
     * @return class-string<RequestEntityInterface>
     */
    private function getEntityClassname(): string
    {
        $classname = $this->entityClassname();
        if (!new $classname() instanceof RequestEntityInterface) {
            throw new RuntimeException(sprintf('The entity "%s" must implement RequestEntityInterface.', $this->entityClassname()));
        }

        /* @phpstan-ignore-next-line */
        return $classname;
    }
}
