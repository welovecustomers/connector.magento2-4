<?php

declare(strict_types=1);

namespace WeLoveCustomers\Connector\Service\Api;

use WeLoveCustomers\Connector\Service\ApiService;

class MagentoPluginVersionApiService
{
    const ENDPOINT_MAGENTO2_PLUGIN_VERSION = 'magento2PluginVersion';

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
    public function getVersion($websiteId, array $params = []): mixed
    {
        return $this->apiService->doRequest(self::ENDPOINT_MAGENTO2_PLUGIN_VERSION, $websiteId, $params, 'POST');
    }
}
