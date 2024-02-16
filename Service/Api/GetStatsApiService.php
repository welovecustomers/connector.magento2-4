<?php

declare(strict_types=1);

namespace WeLoveCustomers\Connector\Service\Api;

use WeLoveCustomers\Connector\Service\ApiService;
use Magento\Framework\Webapi\Rest\Request;

class GetStatsApiService
{
    const ENDPOINT_GET_STATS = 'getStats';

    /**
     * @var ApiService
     */
    protected ApiService $apiService;

    /**
     * @param ApiService $apiService
     */
    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * @param $websiteId
     * @param array $params
     * @return mixed
     */
    public function getStats($websiteId, array $params = []): mixed
    {
        return $this->apiService->doRequest(self::ENDPOINT_GET_STATS, $websiteId, $params, Request::HTTP_METHOD_POST);
    }
}
