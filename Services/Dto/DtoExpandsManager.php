<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\Services\Dto;

use Mell\Bundle\SimpleDtoBundle\Model\Dto;
use Mell\Bundle\SimpleDtoBundle\Model\DtoInterface;
use Mell\Bundle\SimpleDtoBundle\Serializer\Mapping\Factory\ClassMetadataFactory;
use Mell\Bundle\SimpleDtoBundle\Serializer\Normalizer\DtoNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Class DtoExpandsManager
 * @package Mell\Bundle\SimpleDtoBundle\Services\Dto
 */
class DtoExpandsManager
{
    /** @var Serializer */
    protected $serializer;
    /** @var ClassMetadataFactory */
    protected $metadataFactory;

    /**
     * DtoExpandsManager constructor.
     * @param Serializer $serializer
     * @param ClassMetadataFactory $metadataFactory
     */
    public function __construct(Serializer $serializer, ClassMetadataFactory $metadataFactory)
    {
        $this->serializer = $serializer;
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * @param Dto $dto
     * @param array $expands
     */
    public function processExpands(Dto $dto, array $expands): void
    {
        $entity = $dto->getOriginalData();
        $metadata = $this->metadataFactory->getMetadataFor(get_class($entity));
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
                DtoNormalizer::FORMAT_DTO,
                ['groups' => [DtoInterface::DTO_GROUP_READ], 'fields' => $fields]
            );
        }

        if (empty($data)) {
            return;
        }

        $dto->setRawData(array_merge($dto->getRawData(), ['_expands' => $data]));
    }
}
