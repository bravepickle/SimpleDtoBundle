<?php

declare(strict_types=1);

namespace Mell\Bundle\SimpleDtoBundle\EventListener;

use Mell\Bundle\SimpleDtoBundle\Model\ApiFilter;
use Mell\Bundle\SimpleDtoBundle\Services\ApiFiltersManager\ApiFiltersManager;
use Mell\Bundle\SimpleDtoBundle\Services\RequestManager\RequestManager;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class ApiFiltersListener
 */
class ApiFiltersListener
{
    const LIST_SUFFIX_LENGTH = 5;

    /** @var RequestManager */
    protected $requestManager;
    /** @var ApiFiltersManager */
    protected $apiFilterManager;
    /** @var RouterInterface */
    protected $router;

    /**
     * ApiFiltersListener constructor.
     * @param RequestManager $requestManager
     * @param ApiFiltersManager $apiFilterManager
     * @param RouterInterface $router
     */
    public function __construct(
        RequestManager $requestManager,
        ApiFiltersManager $apiFilterManager,
        RouterInterface $router
    ) {
        $this->requestManager = $requestManager;
        $this->apiFilterManager = $apiFilterManager;
        $this->router = $router;
    }

    /**
     * @param ControllerEvent $controllerEvent
     */
    public function onKernelController(ControllerEvent $controllerEvent): void
    {
        try {
            if ($this->router instanceof RequestMatcherInterface) {
                $routeParams = $this->router->matchRequest($controllerEvent->getRequest());
            } else {
                $routeParams = $this->router->match($controllerEvent->getRequest()->getPathInfo());
            }
        } catch (\Exception $e) {
            return;
        }
        if (!$filters = $routeParams['filters'] ?? null) {
            return;
        }

        $filtersCollection = $this->apiFilterManager->parse($this->requestManager->getApiFilters());
        /** @var ApiFilter $apiFilter */
        foreach ($filtersCollection as $i => $apiFilter) {
            if (!in_array($apiFilter->getParam(), $filters)) {
                $filtersCollection->offsetUnset($i);
            }
        }
        $controllerEvent->getRequest()->attributes->set('apiFilters', $filtersCollection);
    }
}
