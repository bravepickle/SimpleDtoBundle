<?php

namespace Mell\Bundle\SimpleDtoBundle\Model;

class DtoManagerConfigurator
{
    /** @var string */
    protected $collectionKey;
    /** @var string */
    protected $formatDate;
    /** @var string */
    protected $formatDateTime;

    /**
     * DtoConfigurator constructor.
     * @param string $collectionKey
     * @param string $formatDate
     * @param string $formatDateTime
     */
    public function __construct($collectionKey, $formatDate, $formatDateTime)
    {
        $this->collectionKey = $collectionKey;
        $this->formatDate = $formatDate;
        $this->formatDateTime = $formatDateTime;
    }

    /**
     * @return string
     */
    public function getCollectionKey()
    {
        return $this->collectionKey;
    }

    /**
     * @param string $collectionKey
     */
    public function setCollectionKey($collectionKey)
    {
        $this->collectionKey = $collectionKey;
    }

    /**
     * @return string
     */
    public function getFormatDate()
    {
        return $this->formatDate;
    }

    /**
     * @param string $formatDate
     */
    public function setFormatDate($formatDate)
    {
        $this->formatDate = $formatDate;
    }

    /**
     * @return string
     */
    public function getFormatDateTime()
    {
        return $this->formatDateTime;
    }

    /**
     * @param string $formatDateTime
     */
    public function setFormatDateTime($formatDateTime)
    {
        $this->formatDateTime = $formatDateTime;
    }
}
