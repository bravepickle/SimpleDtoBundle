<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\Services\Dto;

use Mell\Bundle\SimpleDtoBundle\Model\Dto;
use Mell\Bundle\SimpleDtoBundle\Model\DtoInterface;
use Mell\Bundle\SimpleDtoBundle\Serializer\Mapping\ClassMetadataDecorator;
use Mell\Bundle\SimpleDtoBundle\Services\ORM\ClassUtils;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class DtoExpandsManager
 */
class DtoExpandsManager
{
    /** @var Serializer */
    protected $serializer;
    /** @var ClassMetadataFactoryInterface */
    protected $metadataFactory;
    /** @var ClassUtils */
    protected $classUtils;

    /**
     * DtoExpandsManager constructor.
     * @param SerializerInterface $serializer
     * @param ClassMetadataFactoryInterface $metadataFactory
     * @param ClassUtils $classUtils
     */
    public function __construct(SerializerInterface $serializer, ClassMetadataFactoryInterface $metadataFactory, ClassUtils $classUtils)
    {
        $this->serializer = $serializer;
        $this->metadataFactory = $metadataFactory;
        $this->classUtils = $classUtils;
    }

    /**
     * @param Dto $dto
     * @param array $expands
     */
    public function processExpands(Dto $dto, array $expands): void
    {
        $entity = $dto->getOriginalData();
        /** @var ClassMetadataDecorator $metadata */
        $metadata = $this->metadataFactory->getMetadataFor($this->classUtils->getClass($entity));
        $data = [];
        foreach ($expands as $expand => $fields) {
            if (!in_array($expand, $metadata->getExpands())) {
                continue;
            }
            $getter = 'get' . ucfirst($expand);
            if (!is_callable([$entity, $getter])) {
                continue;
            }
            $object = call_user_func([$entity, $getter]);
            $data[$expand] = $this->serializer->normalize(
                $object,
                null,
                array_merge(['groups' => [DtoInterface::DTO_GROUP_READ],], $fields ? ['attributes' => $fields] : [])
            );
        }

        if (empty($data)) {
            return;
        }

        $dto->setRawData(array_merge($dto->getRawData(), ['_expands' => $data]));
    }
}
