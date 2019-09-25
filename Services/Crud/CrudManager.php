<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\Services\Crud;

use Doctrine\ORM\EntityManagerInterface;
use Mell\Bundle\SimpleDtoBundle\Event\ApiEvent;
use Mell\Bundle\SimpleDtoBundle\Model\Dto;
use Mell\Bundle\SimpleDtoBundle\Model\DtoInterface;
use Mell\Bundle\SimpleDtoBundle\Model\DtoSerializableInterface;
use Mell\Bundle\SimpleDtoBundle\Model\Options\CrudActionOptions;
use Mell\Bundle\SimpleDtoBundle\Services\Dto\DtoManager;
use Mell\Bundle\SimpleDtoBundle\Services\RequestManager\RequestManager;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class CrudManager
 */
class CrudManager
{
    const FORMAT_JSON = 'json';
    const FORMAT_XML = 'xml';
    const CONTENT_TYPE_JSON = 'application/json';
    const CONTENT_TYPE_XML = 'application/xml';

    /** @var EntityManagerInterface */
    protected $entityManager;
    /** @var ValidatorInterface */
    protected $validator;
    /** @var SerializerInterface */
    protected $serializer;
    /** @var EventDispatcherInterface|EventDispatcher */
    protected $eventDispatcher;
    /** @var DtoManager */
    protected $dtoManager;
    /** @var RequestManager */
    protected $requestManager;

    /**
     * CrudManager constructor.
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     * @param SerializerInterface $serializer
     * @param EventDispatcherInterface $eventDispatcher
     * @param DtoManager $dtoManager
     * @param RequestManager $requestManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        SerializerInterface $serializer,
        EventDispatcherInterface $eventDispatcher,
        DtoManager $dtoManager,
        RequestManager $requestManager
    ) {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->serializer = $serializer;
        $this->eventDispatcher = $eventDispatcher;
        $this->dtoManager = $dtoManager;
        $this->requestManager = $requestManager;
    }

    /**
     * @param DtoSerializableInterface $entity
     * @param array $data
     * @param null|CrudActionOptions $options
     * @return DtoSerializableInterface|ConstraintViolationListInterface
     */
    public function createResource(
        DtoSerializableInterface $entity,
        array $data,
        ?CrudActionOptions $options = null
    ) {
        $options = $this->initActionOptions($options);
        $event = new ApiEvent($entity, ApiEvent::ACTION_CREATE, [
            'group' => $options->getDtoGroup(DtoInterface::DTO_GROUP_CREATE)
        ]);

        $this->eventDispatcher->dispatch(ApiEvent::EVENT_PRE_DESERIALIZE, $event);
        $entity = $event->getData(); // in case entity was changed

        $entity = $this->dtoManager->deserializeEntity(
            $entity,
            $this->serializer->serialize($data, $options->format),
            $options->format,
            DtoInterface::DTO_GROUP_CREATE
        );

        $event->setData($entity);
        $this->eventDispatcher->dispatch(ApiEvent::EVENT_POST_DESERIALIZE, $event);
        $entity = $event->getData();

        $options->runCallable($entity);

        $this->eventDispatcher->dispatch(ApiEvent::EVENT_PRE_VALIDATE, $event);
        $entity = $event->getData();

        $errors = $this->validator->validate($entity, null, $options->validationGroups);
        if ($errors->count()) {
            return $errors;
        }

        $this->eventDispatcher->dispatch(ApiEvent::EVENT_PRE_PERSIST, $event);
        $entity = $event->getData();
        $this->entityManager->persist($entity);

        $this->eventDispatcher->dispatch(ApiEvent::EVENT_PRE_FLUSH, $event);
        $this->entityManager->flush();

        $event = new ApiEvent($event->getData(), ApiEvent::ACTION_CREATE);
        $this->eventDispatcher->dispatch(ApiEvent::EVENT_POST_FLUSH, $event);
        $entity = $event->getData();

        return $entity;
    }

    /**
     * @param DtoSerializableInterface $entity
     * @param null|CrudActionOptions $options
     * @return Dto
     */
    public function readResource(DtoSerializableInterface $entity, ?CrudActionOptions $options = null): Dto
    {
        $options = $this->initActionOptions($options);

        $event = new ApiEvent($entity, ApiEvent::ACTION_READ);
        $this->eventDispatcher->dispatch(ApiEvent::EVENT_POST_READ, $event);
        $entity = $event->getData();

        return $this->dtoManager->createDto(
            $entity,
            $options->getDtoGroup(DtoInterface::DTO_GROUP_READ),
            $this->requestManager->getFields()
        );
    }

    /**
     * @param DtoSerializableInterface $entity
     * @param array $data
     * @param null|CrudActionOptions $options
     * @return DtoSerializableInterface|ConstraintViolationListInterface
     */
    public function updateResource(
        DtoSerializableInterface $entity,
        array $data,
        ?CrudActionOptions $options = null
    ) {
        $options = $this->initActionOptions($options);

        $event = new ApiEvent($entity, ApiEvent::ACTION_UPDATE, [
            'group' => $options->getDtoGroup(DtoInterface::DTO_GROUP_UPDATE)
        ]);

        $this->eventDispatcher->dispatch(ApiEvent::EVENT_PRE_DESERIALIZE, $event);
        $entity = $event->getData();

        $entity = $this->dtoManager->deserializeEntity(
            $entity,
            $this->serializer->serialize($data, $options->format),
            $options->format,
            $options->getDtoGroup(DtoInterface::DTO_GROUP_UPDATE)
        );

        $event->setData($entity);
        $this->eventDispatcher->dispatch(ApiEvent::EVENT_POST_DESERIALIZE, $event);
        $entity = $event->getData();

        $options->runCallable($entity);

        $this->eventDispatcher->dispatch(ApiEvent::EVENT_PRE_VALIDATE, $event);
        $entity = $event->getData();

        $errors = $this->validator->validate($entity, null, $options->validationGroups);
        if ($errors->count()) {
            return $errors;
        }

        $this->eventDispatcher->dispatch(ApiEvent::EVENT_PRE_FLUSH, $event);
        $this->entityManager->flush();

        $event = new ApiEvent($event->getData(), ApiEvent::ACTION_UPDATE);

        $this->eventDispatcher->dispatch(ApiEvent::EVENT_POST_FLUSH, $event);
        $entity = $event->getData();

        return $entity;
    }

    /**
     * @param $entity
     */
    public function deleteResource($entity): void
    {
        $this->entityManager->remove($entity);

        $event = new ApiEvent($entity, ApiEvent::ACTION_DELETE);
        $this->eventDispatcher->dispatch(ApiEvent::EVENT_PRE_FLUSH, $event);
        $entity = $event->getData();

        $this->entityManager->flush();
        $event = new ApiEvent($entity, ApiEvent::ACTION_DELETE);
        $this->eventDispatcher->dispatch(ApiEvent::EVENT_POST_FLUSH, $event);
    }

    /**
     * Initialize options
     * @param CrudActionOptions|null $options
     * @return CrudActionOptions
     */
    public function initActionOptions(?CrudActionOptions $options): CrudActionOptions
    {
        if ($options) {
            return $options;
        }

        return new CrudActionOptions(); // options with defaults
    }
}
