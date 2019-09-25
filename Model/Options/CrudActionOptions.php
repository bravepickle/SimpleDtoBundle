<?php

namespace Mell\Bundle\SimpleDtoBundle\Model\Options;


use Mell\Bundle\SimpleDtoBundle\Services\Crud\CrudManager;

/**
 * Class CrudActionOptions contains a set of optional parameters for
 * handling CRUD manager's actions
 * @package Mell\Bundle\SimpleDtoBundle\Model\Options
 */
class CrudActionOptions
{
    /**
     * @var string
     */
    public $dtoGroup;

    /**
     * Run access checker
     * @var null|callable
     */
    public $callable;

    /**
     * Data format
     * @var string
     */
    public $format = CrudManager::FORMAT_JSON;

    /**
     * Used validation groups
     * @var null|array
     */
    public $validationGroups = null;

    /**
     * Run access checker if defined
     * @param $entity
     */
    public function runCallable($entity): void
    {
        if ($this->callable) {
            call_user_func($this->callable, $entity);
        }
    }

    /**
     * Get DTO group with fallback
     * @param string|null $default
     * @return string|null
     */
    public function getDtoGroup(?string $default = null)
    {
        return $this->dtoGroup ?? $default;
    }

    /**
     * @param string $dtoGroup
     * @return CrudActionOptions
     */
    public function setDtoGroup(string $dtoGroup): CrudActionOptions
    {
        $this->dtoGroup = $dtoGroup;

        return $this;
    }

    /**
     * @param callable|null $callable
     * @return CrudActionOptions
     */
    public function setCallable(?callable $callable): CrudActionOptions
    {
        $this->callable = $callable;

        return $this;
    }

    /**
     * @param string $format
     * @return CrudActionOptions
     */
    public function setFormat(string $format): CrudActionOptions
    {
        $this->format = $format;

        return $this;
    }

    /**
     * @param array|null $validationGroups
     * @return CrudActionOptions
     */
    public function setValidationGroups(?array $validationGroups): CrudActionOptions
    {
        $this->validationGroups = $validationGroups;

        return $this;
    }

}