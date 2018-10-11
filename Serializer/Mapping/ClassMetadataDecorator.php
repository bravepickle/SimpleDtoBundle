<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\Serializer\Mapping;

use Mell\Bundle\SimpleDtoBundle\Model\Relation;
use Symfony\Component\Serializer\Mapping\AttributeMetadataInterface;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorMapping;
use Symfony\Component\Serializer\Mapping\ClassMetadataInterface;

/**
 * Class ClassMetadata  
 */
class ClassMetadataDecorator implements ClassMetadataInterface
{
    /** @var ClassMetadataInterface */
    protected $decorated;
    /** @var array */
    protected $expands = [];
    /** @var array */
    protected $links = [];
    /** @var array */
    protected $relations = [];
    /** @var ClassDiscriminatorMapping|null */
    protected $dicriminatorMapping;

    /**
     * ClassMetadata constructor.
     * @param ClassMetadataInterface $decorated
     */
    public function __construct(ClassMetadataInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function getClassDiscriminatorMapping()
    {
        return $this->dicriminatorMapping;
    }

    /**
     * @param ClassDiscriminatorMapping|null $mapping
     * @return $this
     */
    public function setClassDiscriminatorMapping(ClassDiscriminatorMapping $mapping = null)
    {
        $this->dicriminatorMapping = $mapping;

        return $this;
    }

    /**
     * @return array
     */
    public function getExpands(): array
    {
        return $this->expands;
    }

    /**
     * @param array $expands
     */
    public function setExpands(array $expands): void
    {
        $this->expands = $expands;
    }

    /**
     * @return array
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * @param array $links
     */
    public function setLinks(array $links): void
    {
        $this->links = $links;
    }

    /**
     * @return Relation[]
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    /**
     * @param array $relations
     */
    public function setRelations(array $relations): void
    {
        $this->relations = $relations;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->decorated->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeMetadata(AttributeMetadataInterface $attributeMetadata): void
    {
        $this->decorated->addAttributeMetadata($attributeMetadata);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributesMetadata(): array
    {
        return $this->decorated->getAttributesMetadata();
    }

    /**
     * {@inheritdoc}
     */
    public function merge(ClassMetadataInterface $classMetadata): void
    {
        $this->decorated->merge($classMetadata);
    }

    /**
     * {@inheritdoc}
     */
    public function getReflectionClass(): \ReflectionClass
    {
        return $this->decorated->getReflectionClass();
    }
}
