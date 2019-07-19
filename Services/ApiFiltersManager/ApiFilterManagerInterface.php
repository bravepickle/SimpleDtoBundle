<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\Services\ApiFiltersManager;

use Mell\Bundle\SimpleDtoBundle\Model\ApiFilterCollectionInterface;

/**
 * Interface ApiFilterManagerInterface
 */
interface ApiFilterManagerInterface
{
    const OPERATION_ALIAS_EQUAL = ':';
    const OPERATION_ALIAS_NOT_EQUAL = '!:';
    const OPERATION_ALIAS_MORE_THEN = '>:';
    const OPERATION_ALIAS_LESS_THEN = '<:';
    const OPERATION_ALIAS_LESS_OR_EQUAL_THEN = '<=:';
    const OPERATION_ALIAS_MORE_OR_EQUAL_THEN = '>=:';

    /**
     * @param string $filtersStr
     * @return ApiFilterCollectionInterface
     */
    public function parse(string $filtersStr): ApiFilterCollectionInterface;
}
